<?php

namespace App\Jobs\Pipeline;

class SyncFixturesJob extends PipelineJob
{
    public $queue = 'normal';

    protected function stage(): string
    {
        return 'sync_fixtures';
    }

    protected function command(): string
    {
        return 'predictions:sync-fixtures';
    }

    protected function params(): array
    {
        return ['--days' => 1];
    }
}
