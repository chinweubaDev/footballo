<?php

namespace App\Console\Commands;

use App\Jobs\Pipeline\AggregatePerformanceJob;
use App\Jobs\Pipeline\GeneratePredictionsJob;
use App\Jobs\Pipeline\GenerateShadowPredictionsJob;
use App\Jobs\Pipeline\LockPredictionsJob;
use App\Jobs\Pipeline\ResolveResultsJob;
use App\Jobs\Pipeline\SyncFixturesJob;
use App\Jobs\Pipeline\UpdateLiveScoresJob;
use Illuminate\Console\Command;

/**
 * Phase 1K — dispatch the full prediction pipeline as queued jobs.
 *
 *   php artisan predictions:pipeline
 */
class DispatchPipeline extends Command
{
    protected $signature = 'predictions:pipeline';

    protected $description = 'Dispatch the full live pipeline as queued jobs';

    public function handle(): int
    {
        SyncFixturesJob::dispatch();
        GeneratePredictionsJob::dispatch();
        GenerateShadowPredictionsJob::dispatch();
        LockPredictionsJob::dispatch();
        UpdateLiveScoresJob::dispatch();
        ResolveResultsJob::dispatch();
        AggregatePerformanceJob::dispatch();

        $this->info('Pipeline jobs dispatched: sync → generate → shadow → lock → live-scores → resolve → aggregate.');

        return 0;
    }
}
