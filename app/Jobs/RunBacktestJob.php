<?php

namespace App\Jobs;

use App\Models\BacktestRun;
use App\Services\Prediction\Evaluation\BacktestEngine;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs a backtest on the queue. Backtests can process thousands of fixtures,
 * so they must never execute inside an HTTP request.
 */
class RunBacktestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $backtestRunId)
    {
    }

    public function handle(BacktestEngine $engine, PerformanceAnalyticsService $performance): void
    {
        $run = BacktestRun::find($this->backtestRunId);

        if (! $run) {
            Log::warning('RunBacktestJob: run not found', ['id' => $this->backtestRunId]);

            return;
        }

        if ($run->status === BacktestRun::STATUS_CANCELLED) {
            return;
        }

        try {
            $engine->run($run);
        } catch (\Throwable $e) {
            Log::error('RunBacktestJob failed', [
                'backtest_run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $engine->fail($run, $e->getMessage());
        }
    }
}
