<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;
use App\Services\Prediction\Support\PoissonMatrix;

class GoalsMarket
{
    public function __construct(
        protected string $code,
        protected float $threshold,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function calculate(EnsembleResult $result): array
    {
        $over = PoissonMatrix::over($result->scoreMatrix, $this->threshold);

        if ($over >= 50.0) {
            return [
                'selection' => 'over_'.str_replace('.', '_', (string) $this->threshold),
                'probability' => $over,
            ];
        }

        return [
            'selection' => 'under_'.str_replace('.', '_', (string) $this->threshold),
            'probability' => round(100.0 - $over, 2),
        ];
    }
}
