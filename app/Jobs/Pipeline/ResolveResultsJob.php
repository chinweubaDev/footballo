<?php

namespace App\Jobs\Pipeline;

class ResolveResultsJob extends PipelineJob
{
    public $queue = 'high';

    protected function stage(): string
    {
        return 'resolve_results';
    }

    protected function command(): string
    {
        return 'predictions:resolve-results';
    }
}
