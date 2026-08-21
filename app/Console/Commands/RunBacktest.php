<?php

namespace App\Console\Commands;

use App\Models\BacktestRun;
use App\Services\Prediction\Evaluation\BacktestEngine;
use Illuminate\Console\Command;

/**
 * Run a backtest synchronously from the CLI (for manual / debugging runs).
 * Normal admin-initiated backtests go through the queue via RunBacktestJob.
 */
class RunBacktest extends Command
{
    protected $signature = 'predictions:backtest
                            {--league= : API-Football league id (default: all)}
                            {--season= : Season year (default: all)}
                            {--from= : Start date Y-m-d}
                            {--to= : End date Y-m-d}
                            {--markets= : Comma-separated market codes}
                            {--min-confidence=0 : Minimum confidence}
                            {--min-probability=0 : Minimum probability}
                            {--model-version= : Model version}';

    protected $description = 'Run a historical backtest synchronously';

    public function handle(BacktestEngine $engine): int
    {
        $modelVersion = $this->option('model-version') ?: config('prediction.model_version', 'v1.0.0');

        $markets = $this->option('markets')
            ? array_filter(array_map('trim', explode(',', $this->option('markets'))))
            : null;

        $run = BacktestRun::create([
            'name' => 'CLI backtest '.now()->toDateTimeString(),
            'league_id' => $this->option('league') ? (int) $this->option('league') : null,
            'season' => $this->option('season') ? (int) $this->option('season') : null,
            'date_start' => $this->option('from') ?: null,
            'date_end' => $this->option('to') ?: null,
            'markets' => $markets,
            'min_confidence' => (int) $this->option('min-confidence'),
            'min_probability' => (float) $this->option('min-probability'),
            'model_version' => $modelVersion,
            'config_snapshot' => config('prediction'),
            'status' => BacktestRun::STATUS_QUEUED,
        ]);

        $this->info("Backtest run #{$run->id} started for model {$modelVersion}.");

        $metrics = $engine->run($run);

        $overview = $metrics['overview'] ?? [];

        $this->table(
            ['Fixtures', 'Predictions', 'Wins', 'Losses', 'Accuracy', 'Coverage', 'Brier'],
            [[
                $run->total_fixtures,
                $overview['total'] ?? 0,
                $overview['won'] ?? 0,
                $overview['lost'] ?? 0,
                $overview['accuracy'] ?? '—',
                ($metrics['coverage_percent'] ?? '—'),
                $overview['brier_score'] ?? '—',
            ]],
        );

        $this->info('Done.');

        return 0;
    }
}
