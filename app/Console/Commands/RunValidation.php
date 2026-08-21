<?php

namespace App\Console\Commands;

use App\Models\BacktestRun;
use App\Models\League;
use App\Services\Prediction\Evaluation\BacktestEngine;
use Illuminate\Console\Command;

/**
 * Runs walk-forward validation backtests across leagues (and optionally
 * seasons). Each run is stored as a BacktestRun for reproducibility.
 */
class RunValidation extends Command
{
    protected $signature = 'predictions:validate
                            {--league= : API-Football league id (omit for all enabled leagues)}
                            {--season=2025 : Season year}
                            {--model-version=v1.0.0 : Model version to evaluate}';

    protected $description = 'Run multi-league walk-forward validation backtests';

    public function handle(BacktestEngine $engine): int
    {
        $season = (int) $this->option('season');
        $modelVersion = $this->option('model-version');
        $leagueOption = $this->option('league');

        $leagueIds = $leagueOption
            ? [(int) $leagueOption]
            : League::query()->where('enabled', true)->pluck('api_football_league_id')->all();

        if (empty($leagueIds)) {
            $this->warn('No leagues to validate.');
            return 1;
        }

        $rows = [];

        foreach ($leagueIds as $leagueId) {
            $run = BacktestRun::create([
                'name' => "Validation {$modelVersion} L{$leagueId} S{$season}",
                'league_id' => $leagueId,
                'season' => $season,
                'markets' => null,
                'min_confidence' => 0,
                'min_probability' => 0,
                'model_version' => $modelVersion,
                'config_snapshot' => config('prediction'),
                'status' => BacktestRun::STATUS_QUEUED,
            ]);

            $metrics = $engine->run($run);

            $overview = $metrics['overview'] ?? [];
            $leagueName = $run->league?->name ?? "League {$leagueId}";

            $rows[] = [
                $leagueName,
                $run->total_fixtures,
                $overview['total'] ?? 0,
                $overview['won'] ?? 0,
                $overview['lost'] ?? 0,
                isset($overview['accuracy']) ? number_format($overview['accuracy'], 2).'%' : '—',
                isset($overview['brier_score']) ? number_format($overview['brier_score'], 4) : '—',
                ($metrics['coverage_percent'] ?? '—'),
            ];
        }

        $this->table(
            ['League', 'Fixtures', 'Predictions', 'Wins', 'Losses', 'Accuracy', 'Brier', 'Coverage'],
            $rows,
        );

        $this->info('Validation complete. See /admin/predictions/validation for the full matrix.');

        return 0;
    }
}
