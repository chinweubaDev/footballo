<?php

namespace App\Services\Prediction\Markets;

use App\Services\Prediction\Support\EnsembleResult;

class CorrectScoreMarket
{
    public function code(): string
    {
        return 'correct_score';
    }

    public function calculate(EnsembleResult $result): array
    {
        $scores = $result->correctScores;

        return [
            'selection' => $scores[0]['score'] ?? null,
            'probability' => $scores[0]['probability'] ?? 0.0,
            'scores' => $scores,
        ];
    }
}
