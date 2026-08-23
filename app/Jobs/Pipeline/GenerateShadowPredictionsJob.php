<?php

namespace App\Jobs\Pipeline;

class GenerateShadowPredictionsJob extends PipelineJob
{
    public $queue = 'normal';

    protected function stage(): string
    {
        return 'generate_shadow';
    }

    protected function command(): string
    {
        return 'predictions:shadow';
    }

    protected function params(): array
    {
        return ['--days' => 1];
    }
}
