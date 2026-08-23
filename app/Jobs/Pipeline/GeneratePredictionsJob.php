<?php

namespace App\Jobs\Pipeline;

class GeneratePredictionsJob extends PipelineJob
{
    public $queue = 'normal';

    protected function stage(): string
    {
        return 'generate_predictions';
    }

    protected function command(): string
    {
        return 'predictions:generate';
    }

    protected function params(): array
    {
        return ['--days' => 1];
    }
}
