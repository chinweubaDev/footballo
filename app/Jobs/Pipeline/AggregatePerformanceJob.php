<?php

namespace App\Jobs\Pipeline;

use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;

class AggregatePerformanceJob extends PipelineJob
{
    public $queue = 'low';

    protected function stage(): string
    {
        return 'aggregate_performance';
    }

    public function handle(): void
    {
        // Warm/recompute the performance dashboard cache.
        app(PerformanceAnalyticsService::class)->dashboard();

        parent::handle();
    }
}
