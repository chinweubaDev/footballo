<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;

class DrawMarket
{
    public function code(): string
    {
        return 'draw';
    }

    public function calculate(EnsembleResult $result): array
    {
        return [
            'selection' => 'draw',
            'probability' => round($result->drawProbability, 2),
        ];
    }
}
