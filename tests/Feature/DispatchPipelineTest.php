<?php

namespace Tests\Feature;

use App\Jobs\Pipeline\AggregatePerformanceJob;
use App\Jobs\Pipeline\GeneratePredictionsJob;
use App\Jobs\Pipeline\GenerateShadowPredictionsJob;
use App\Jobs\Pipeline\LockPredictionsJob;
use App\Jobs\Pipeline\ResolveResultsJob;
use App\Jobs\Pipeline\SyncFixturesJob;
use App\Jobs\Pipeline\UpdateLiveScoresJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchPipelineTest extends TestCase
{
    public function test_pipeline_dispatches_all_seven_jobs(): void
    {
        Queue::fake();

        $this->artisan('predictions:pipeline')->assertSuccessful();

        Queue::assertPushed(SyncFixturesJob::class);
        Queue::assertPushed(GeneratePredictionsJob::class);
        Queue::assertPushed(GenerateShadowPredictionsJob::class);
        Queue::assertPushed(LockPredictionsJob::class);
        Queue::assertPushed(UpdateLiveScoresJob::class);
        Queue::assertPushed(ResolveResultsJob::class);
        Queue::assertPushed(AggregatePerformanceJob::class);
    }
}
