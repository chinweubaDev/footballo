<?php

namespace App\Jobs\Pipeline;

class LockPredictionsJob extends PipelineJob
{
    public $queue = 'high';

    protected function stage(): string
    {
        return 'lock_predictions';
    }

    protected function command(): string
    {
        return 'predictions:lock';
    }

    protected function params(): array
    {
        return ['--window' => 30];
    }
}
