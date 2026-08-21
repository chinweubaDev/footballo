<?php

namespace App\Services\Prediction\Validation;

use App\Models\BacktestRun;
use App\Services\Prediction\Evaluation\BacktestEngine;
use App\Services\Prediction\FeatureEngine;

/**
 * Ablation validation (Phase 1G §36).
 *
 * Runs the full model plus one backtest per ablatable feature group
 * (form, h2h, team_strength, xg) and compares Accuracy / Brier / Calibration /
 * Coverage. Only features that actually exist in the feature vector are
 * ablated — "standings" has no standalone feature and is skipped.
 *
 * Every run is persisted as a BacktestRun with its config snapshot so the
 * comparison remains reproducible.
 */
class AblationService
{
    public function __construct(protected BacktestEngine $engine)
    {
    }

    /**
     * @param list<string>|null $markets
     * @return array<string,mixed>
     */
    public function run(?int $leagueId = null, ?int $season = null, string $modelVersion = 'v1.0.0', ?array $markets = null): array
    {
        $full = $this->runOne('Full model', [], $leagueId, $season, $modelVersion, $markets);

        $results = ['full' => $full['comparison']];
        $runs = ['full' => $full['run_id']];

        foreach (FeatureEngine::ABLATABLE as $group) {
            $ab = $this->runOne("No {$group}", [$group], $leagueId, $season, $modelVersion, $markets);

            $results["no_{$group}"] = $ab['comparison'];
            $runs["no_{$group}"] = $ab['run_id'];
        }

        return [
            'model_version' => $modelVersion,
            'league_id' => $leagueId,
            'season' => $season,
            'groups' => FeatureEngine::ABLATABLE,
            'results' => $results,
            'runs' => $runs,
        ];
    }

    /**
     * @param list<string> $ablated
     * @param list<string>|null $markets
     * @return array{run_id:int,comparison:array<string,mixed>}
     */
    protected function runOne(string $name, array $ablated, ?int $leagueId, ?int $season, string $modelVersion, ?array $markets): array
    {
        $run = BacktestRun::create([
            'name' => "Ablation: {$name} {$modelVersion}",
            'league_id' => $leagueId,
            'season' => $season,
            'markets' => $markets,
            'min_confidence' => 0,
            'min_probability' => 0,
            'model_version' => $modelVersion,
            'config_snapshot' => array_merge(config('prediction'), ['ablations' => $ablated]),
            'status' => BacktestRun::STATUS_QUEUED,
        ]);

        $metrics = $this->engine->run($run);

        $overview = $metrics['overview'] ?? [];

        return [
            'run_id' => $run->id,
            'comparison' => [
                'name' => $name,
                'ablated' => $ablated,
                'accuracy' => $overview['accuracy'] ?? null,
                'resolved' => $overview['resolved'] ?? 0,
                'won' => $overview['won'] ?? 0,
                'lost' => $overview['lost'] ?? 0,
                'brier_score' => $overview['brier_score'] ?? null,
                'log_loss' => $overview['log_loss'] ?? null,
                'calibration_error' => $overview['calibration_error'] ?? null,
                'coverage' => $metrics['coverage_percent'] ?? null,
            ],
        ];
    }
}
