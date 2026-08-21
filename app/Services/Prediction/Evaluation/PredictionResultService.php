<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\Admin\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Resolves live predictions against final fixture results.
 *
 * Responsibilities:
 *   - resolvePrediction()        resolve one prediction against a fixture
 *   - resolveFixturePredictions() resolve every prediction for a fixture
 *   - resolveCompletedFixtures()  resolve all completed fixtures (idempotent)
 *
 * Outcomes: won / lost / void / pending.
 * Results are immutable: a later correction (e.g. API-Football correcting an
 * official result) is recorded in `result_corrections` without destroying the
 * original audit trail.
 */
class PredictionResultService
{
    public const WON = 'won';
    public const LOST = 'lost';
    public const VOID = 'void';
    public const PENDING = 'pending';

    /** Internal fixture classification (not an outcome). */
    private const TERMINAL = 'terminal';

    public function __construct(
        protected MarketResultResolver $resolver,
        protected AuditLogger $audit,
    ) {
    }

    /**
     * Resolve a single prediction. Returns won|lost|void|pending.
     * Only writes to the database when the fixture has a final (or void) state.
     */
    public function resolvePrediction(Prediction $prediction, ?Fixture $fixture = null): string
    {
        $fixture ??= $prediction->fixture;

        if (! $fixture) {
            return self::PENDING;
        }

        $state = $this->fixtureState($fixture);

        if ($state === self::PENDING) {
            return self::PENDING;
        }

        $score = "{$fixture->home_goals}-{$fixture->away_goals}";

        if ($state === self::VOID) {
            $this->persist($prediction, self::VOID, self::VOID, null, $score, $fixture->status);

            return self::VOID;
        }

        $model = $this->resolveSelection($prediction->market_code, $prediction->selection, $fixture);
        $override = $prediction->admin_selection !== null
            ? $this->resolveSelection($prediction->market_code, $prediction->admin_selection, $fixture)
            : null;

        // The selection actually shown/used: admin override wins.
        $effective = $override ?? $model;

        // A terminal fixture whose selection cannot be structurally resolved
        // (e.g. legacy rows with no structured selection) is recorded as void
        // with a reason rather than being guessed via text matching.
        $voidReason = $effective === self::VOID ? 'unresolvable_selection' : null;

        $this->persist($prediction, $effective, $model, $override, $score, $voidReason);

        return $effective;
    }

    /**
     * Resolve every prediction attached to a fixture. Returns the number of
     * predictions that were resolved (transitioned out of pending) in this call.
     */
    public function resolveFixturePredictions(Fixture $fixture): int
    {
        $resolved = 0;

        $fixture->predictions()
            ->whereNull('result')
            ->select(['id', 'fixture_id', 'market_code', 'selection', 'admin_selection'])
            ->chunkById(200, function ($predictions) use ($fixture, &$resolved) {
                foreach ($predictions as $prediction) {
                    if ($this->resolvePrediction($prediction, $fixture) !== self::PENDING) {
                        $resolved++;
                    }
                }
            });

        return $resolved;
    }

    /**
     * Resolve all completed fixtures that still have unresolved predictions.
     * Idempotent: running twice never duplicates results.
     *
     * @return array{fixtures:int,predictions:int}
     */
    public function resolveCompletedFixtures(?int $limit = null): array
    {
        $statuses = array_merge(
            config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']),
            config('evaluation.void_statuses', ['PST', 'CANC', 'ABD']),
        );

        $fixtures = 0;
        $predictions = 0;

        Fixture::query()
            ->whereIn('status', $statuses)
            ->whereHas('predictions', fn ($q) => $q->whereNull('result'))
            ->select(['id', 'status', 'home_goals', 'away_goals'])
            ->when($limit, fn ($q) => $q->limit($limit))
            ->chunkById(200, function ($rows) use (&$fixtures, &$predictions) {
                foreach ($rows as $row) {
                    $fixtures++;
                    $predictions += $this->resolveFixturePredictions($row);
                }
            });

        return [
            'fixtures' => $fixtures,
            'predictions' => $predictions,
        ];
    }

    /**
     * Classify a fixture into pending / void / terminal for resolution.
     */
    protected function fixtureState(Fixture $fixture): string
    {
        $voidStatuses = config('evaluation.void_statuses', ['PST', 'CANC', 'ABD', 'SUSP', 'WO', 'AWD']);
        $terminalStatuses = config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']);

        if (in_array($fixture->status, $voidStatuses, true)) {
            return self::VOID;
        }

        if (! in_array($fixture->status, $terminalStatuses, true)) {
            return self::PENDING;
        }

        // Terminal but score not yet synced — wait for the score sync job.
        if ($fixture->home_goals === null || $fixture->away_goals === null) {
            return self::PENDING;
        }

        return self::TERMINAL;
    }

    protected function resolveSelection(?string $marketCode, ?string $selection, Fixture $fixture): string
    {
        if ($marketCode === null || $selection === null) {
            return self::VOID;
        }

        return $this->resolver->resolve($marketCode, $selection, (int) $fixture->home_goals, (int) $fixture->away_goals);
    }

    /**
     * Persist the resolution while preserving the correction audit trail.
     */
    protected function persist(
        Prediction $prediction,
        string $effective,
        string $model,
        ?string $override,
        string $score,
        ?string $voidReason,
    ): void {
        $corrections = $prediction->result_corrections ?? [];

        // Immutability: never silently overwrite a previous result. Record the
        // correction so the original audit trail is preserved.
        if ($prediction->result !== null && $prediction->result !== $effective) {
            $corrections[] = [
                'previous_result' => $prediction->result,
                'new_result' => $effective,
                'previous_actual_score' => $prediction->actual_score,
                'new_actual_score' => $score,
                'corrected_at' => now()->toDateTimeString(),
                'reason' => 'api_football_result_correction',
            ];

            $this->audit->log($prediction, 'result_corrected', [
                'previous_result' => $prediction->result,
                'new_result' => $effective,
                'previous_actual_score' => $prediction->actual_score,
                'new_actual_score' => $score,
            ]);
        }

        $prediction->update([
            'result' => $effective,
            'model_result' => $model,
            'override_result' => $override,
            'actual_score' => $score,
            'resolved_at' => now(),
            'void_reason' => $voidReason,
            'result_corrections' => $corrections,
        ]);
    }
}
