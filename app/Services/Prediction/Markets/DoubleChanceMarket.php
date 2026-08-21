<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;

class DoubleChanceMarket
{
    public function code(): string
    {
        return 'double_chance';
    }

    public function calculate(EnsembleResult $result): array
    {
        $probs = $result->doubleChance;

        arsort($probs);
        $selection = (string) array_key_first($probs);

        return [
            'selection' => $selection,
            'probability' => round((float) reset($probs), 2),
        ];
    }
}
