<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;

class OneXTwoMarket
{
    public function code(): string
    {
        return '1x2';
    }

    public function calculate(EnsembleResult $result): array
    {
        $probs = [
            'home' => $result->homeProbability,
            'draw' => $result->drawProbability,
            'away' => $result->awayProbability,
        ];

        arsort($probs);
        $selection = (string) array_key_first($probs);

        return [
            'selection' => $selection,
            'probability' => round((float) reset($probs), 2),
        ];
    }
}
