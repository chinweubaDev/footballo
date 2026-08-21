<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;

class BttsMarket
{
    public function code(): string
    {
        return 'btts';
    }

    public function calculate(EnsembleResult $result): array
    {
        if ($result->bttsProbability >= 50.0) {
            return [
                'selection' => 'yes',
                'probability' => $result->bttsProbability,
            ];
        }

        return [
            'selection' => 'no',
            'probability' => round(100.0 - $result->bttsProbability, 2),
        ];
    }
}
