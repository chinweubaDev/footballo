<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\Fixture;
use App\Models\League;
use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\FeatureEngine;
use App\Services\Prediction\Markets\BttsMarket;
use App\Services\Prediction\Markets\CorrectScoreMarket;
use App\Services\Prediction\Markets\DoubleChanceMarket;
use App\Services\Prediction\Markets\DrawMarket;
use App\Services\Prediction\Markets\GoalsMarket;
use App\Services\Prediction\Markets\OneXTwoMarket;
use App\Services\Prediction\Models\EnsemblePredictionModel;
use Illuminate\Support\Facades\Log;

/**
 * Executes a backtest run over historical fixtures.
 *
 * Safety guarantees:
 *   - Operates ONLY on the backtest_predictions table — live predictions,
 *     published predictions, admin overrides and homepage selections are
 *     never touched.
 *   - Walk-forward evaluation: each fixture's context is built only from
 *     fixtures that finished before its kickoff (no future-data leakage).
 *   - Reproducible: weights/thresholds are read from the run's config snapshot.
 */
class BacktestEngine
{
    protected const KNOWN_MARKETS = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];

    public function __construct(
        protected BacktestDataCollector $collector,
        protected FeatureEngine $features,
        protected EnsemblePredictionModel $ensemble,
        protected ConfidenceEngine $confidence,
        protected MarketResultResolver $resolver,
        protected MetricsCalculator $metrics,
    ) {
    }

    /**
     * Run the backtest synchronously (used by the queue job). Returns the
     * computed metrics array.
     *
     * @return array<string,mixed>
     */
    public function run(BacktestRun $run): array
    {
        $run->update([
            'status' => BacktestRun::STATUS_RUNNING,
            'started_at' => now(),
            'completed_at' => null,
            'error' => null,
            'metrics' => null,
        ]);

        // Idempotency: a re-run starts from a clean slate for this run only.
        BacktestPrediction::where('backtest_run_id', $run->id)->delete();

        $total = $this->eligibleCount($run);
        $run->update(['total_fixtures' => $total]);

        // Preload the walk-forward dataset once (avoids per-fixture DB reads).
        $this->collector->warm($run->league_id, $run->season);

        if ($total === 0) {
            return $this->finish($run, [
                'insufficient_data' => true,
                'message' => 'Insufficient historical data for this backtest.',
            ], BacktestRun::STATUS_COMPLETED);
        }

        $processed = 0;
        $generated = 0;
        $now = now();

        $this->eligibleQuery($run)
            ->chunkById(50, function ($fixtures) use ($run, &$processed, &$generated, $now) {
                // Cooperative cancellation is checked once per chunk.
                if ($run->fresh()->status === BacktestRun::STATUS_CANCELLED) {
                    return false;
                }

                $buffer = [];

                foreach ($fixtures as $fixture) {
                    try {
                        $rows = $this->buildRows($run, $fixture, $now);
                        $buffer = array_merge($buffer, $rows);
                        $generated += count($rows);
                    } catch (\Throwable $e) {
                        Log::warning('Backtest fixture skipped', [
                            'backtest_run_id' => $run->id,
                            'fixture_id' => $fixture->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $processed++;
                }

                if (! empty($buffer)) {
                    BacktestPrediction::insert($buffer);
                }

                $run->update([
                    'processed_fixtures' => $processed,
                    'generated_predictions' => $generated,
                ]);
            });

        // If cancelled mid-run, stop without producing metrics.
        if ($run->fresh()->status === BacktestRun::STATUS_CANCELLED) {
            $run->update(['completed_at' => now()]);

            return [];
        }

        $resolved = BacktestPrediction::where('backtest_run_id', $run->id)->whereNotNull('result')->count();

        return $this->finish($run, [
            'resolved_predictions' => $resolved,
            'metrics' => $this->computeMetrics($run),
        ], BacktestRun::STATUS_COMPLETED);
    }

    /**
     * Fail the run with an error message (preserving partial progress).
     */
    public function fail(BacktestRun $run, string $message): void
    {
        $run->update([
            'status' => BacktestRun::STATUS_FAILED,
            'error' => $message,
            'completed_at' => now(),
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    protected function finish(BacktestRun $run, array $payload, string $status): array
    {
        $update = ['status' => $status, 'completed_at' => now()];

        if (isset($payload['resolved_predictions'])) {
            $update['resolved_predictions'] = $payload['resolved_predictions'];
        }

        if (isset($payload['metrics'])) {
            $update['metrics'] = $payload['metrics'];
        }

        if (isset($payload['insufficient_data'])) {
            $update['error'] = $payload['message'] ?? 'Insufficient historical data for this backtest.';
        }

        $run->update($update);

        return $payload['metrics'] ?? [];
    }

    /**
     * Generate and resolve every selected market for one historical fixture,
     * returning the rows to persist (bulk-inserted by the caller).
     *
     * @return list<array<string,mixed>>
     */
    protected function buildRows(BacktestRun $run, Fixture $fixture, \Illuminate\Support\Carbon $now): array
    {
        $context = $this->collector->collect($fixture);

        $snapshot = $run->config_snapshot ?? [];
        // Ablation validation (Phase 1G §36): disable feature groups from the
        // snapshot so a run can measure each feature's out-of-sample impact.
        $ablated = is_array($snapshot['ablations'] ?? null) ? $snapshot['ablations'] : [];

        $features = $this->features->build($context, $ablated);
        $ensemble = $this->ensemble->predict($context, $features, $this->resolveWeights($run));
        $minConfidence = (int) ($run->min_confidence ?? $snapshot['min_confidence'] ?? 0);
        $minProbability = (float) ($run->min_probability ?? $snapshot['min_probability'] ?? 0);
        // Backtests evaluate the model's raw predictions. Unlike live
        // publishing, data quality is recorded for analysis but is NOT used as
        // a bet gate (historical odds/API-AI/injuries are unavailable, which
        // would otherwise cap data quality and mark everything NO_BET).
        $minDataQuality = 0;

        $score = "{$fixture->home_goals}-{$fixture->away_goals}";
        $rows = [];

        foreach ($this->markets($run) as $code) {
            $market = $this->marketFor($code);

            if ($market === null) {
                continue;
            }

            $calculated = $market->calculate($ensemble);
            $selection = $calculated['selection'];

            if ($selection === null) {
                continue;
            }

            $probability = (float) $calculated['probability'];
            $confidence = $this->confidence->calculate($context, $code, $selection, $probability, $ensemble);
            $status = $this->decideStatus($probability, $confidence['score'], $ensemble->dataQuality, $minConfidence, $minProbability, $minDataQuality);

            $result = $this->resolver->resolve($code, $selection, (int) $fixture->home_goals, (int) $fixture->away_goals);

            $rows[] = [
                'backtest_run_id' => $run->id,
                'fixture_id' => $fixture->id,
                'market_code' => $code,
                'selection' => $selection,
                'probability' => $probability,
                'confidence' => $confidence['score'],
                'model_version' => $run->model_version,
                'data_quality_score' => $ensemble->dataQuality,
                'prediction_data' => isset($calculated['scores']) ? json_encode(['scores' => $calculated['scores']]) : null,
                'status' => $status,
                'result' => $result,
                'actual_score' => $score,
                'predicted_at' => $fixture->match_date?->toDateTimeString(),
                'resolved_at' => $now->toDateTimeString(),
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];
        }

        return $rows;
    }

    protected function decideStatus(
        float $probability,
        int $confidence,
        int $dataQuality,
        int $minConfidence,
        float $minProbability,
        int $minDataQuality,
    ): string {
        if ($probability < $minProbability || $confidence < $minConfidence || $dataQuality < $minDataQuality) {
            return 'no_bet';
        }

        return 'generated';
    }

    /**
     * @return array<string,mixed>
     */
    protected function computeMetrics(BacktestRun $run): array
    {
        $rows = BacktestPrediction::where('backtest_run_id', $run->id)
            ->whereNotNull('result')
            ->get([
                'market_code', 'selection', 'probability', 'confidence',
                'model_version', 'data_quality_score', 'result', 'status',
            ])
            ->map(fn ($p) => [
                'market_code' => $p->market_code,
                'selection' => $p->selection,
                'probability' => (float) $p->probability,
                'confidence' => (int) $p->confidence,
                'model_version' => $p->model_version,
                'data_quality_score' => $p->data_quality_score,
                'result' => $p->result,
                'status' => $p->status,
            ])
            ->all();

        $bettable = array_values(array_filter($rows, fn ($r) => ($r['status'] ?? null) !== 'no_bet'));
        $noBet = array_values(array_filter($rows, fn ($r) => ($r['status'] ?? null) === 'no_bet'));

        $overview = $this->metrics->summarize($bettable);

        // Coverage: fixtures with at least one bettable prediction / eligible fixtures.
        $bettableFixtures = BacktestPrediction::where('backtest_run_id', $run->id)
            ->whereNotNull('result')
            ->where('status', '!=', 'no_bet')
            ->distinct('fixture_id')
            ->count('fixture_id');

        $eligible = (int) $run->total_fixtures;

        return [
            'overview' => $overview,
            'coverage_percent' => $eligible > 0 ? round($bettableFixtures / $eligible * 100, 2) : null,
            'coverage' => [
                'eligible_fixtures' => $eligible,
                'predicted_fixtures' => $bettableFixtures,
            ],
            'no_bet' => [
                'count' => count($noBet),
                'would_be' => $this->metrics->summarize($noBet),
            ],
            'by_market' => $this->metrics->byMarket($bettable),
            'by_model_version' => $this->metrics->byModelVersion($bettable),
            'confidence_buckets' => $this->metrics->confidenceBuckets($bettable),
            'probability_buckets' => $this->metrics->probabilityBuckets($bettable),
            'selectivity' => $this->metrics->selectivity($bettable),
        ];
    }

    /**
     * @return list<string>
     */
    protected function markets(BacktestRun $run): array
    {
        $markets = $run->markets;

        if (is_array($markets) && ! empty($markets)) {
            return array_values(array_filter($markets, fn ($m) => in_array($m, self::KNOWN_MARKETS, true)));
        }

        return self::KNOWN_MARKETS;
    }

    /**
     * Ensemble weights from the config snapshot (reproducibility).
     *
     * @return array<string,float>
     */
    protected function resolveWeights(BacktestRun $run): array
    {
        $snapshot = $run->config_snapshot ?? [];

        $weights = $snapshot['ensemble']['weights'] ?? config('prediction.ensemble.weights', []);

        return is_array($weights) ? $weights : [];
    }

    protected function marketFor(string $code): object|null
    {
        return match ($code) {
            '1x2' => new OneXTwoMarket(),
            'draw' => new DrawMarket(),
            'double_chance' => new DoubleChanceMarket(),
            'over_1_5' => new GoalsMarket('over_1_5', 1.5),
            'over_2_5' => new GoalsMarket('over_2_5', 2.5),
            'btts' => new BttsMarket(),
            'correct_score' => new CorrectScoreMarket(),
            default => null,
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function eligibleQuery(BacktestRun $run)
    {
        return Fixture::query()
            ->whereIn('status', config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']))
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->whereNotNull('home_team_id')
            ->whereNotNull('away_team_id')
            ->when($run->league_id, fn ($q) => $q->where('league_id', $run->league_id))
            ->when($run->season, fn ($q) => $q->where('season', (int) $run->season))
            ->when($run->date_start, fn ($q) => $q->where('match_date', '>=', $run->date_start->startOfDay()))
            ->when($run->date_end, fn ($q) => $q->where('match_date', '<=', $run->date_end->endOfDay()));
    }

    protected function eligibleCount(BacktestRun $run): int
    {
        return (int) $this->eligibleQuery($run)->count();
    }
}
