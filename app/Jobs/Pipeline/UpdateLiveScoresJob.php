<?php

namespace App\Jobs\Pipeline;

class UpdateLiveScoresJob extends PipelineJob
{
    public $queue = 'high';

    protected function stage(): string
    {
        return 'update_live_scores';
    }

    protected function command(): string
    {
        return 'scores:update-live';
    }
}
