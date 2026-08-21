<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class OddsPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'odds';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        if (! ($context->odds['available'] ?? false)) {
            return new ModelPrediction(
                name: $this->name(),
                homeProbability: 33.3,
                drawProbability: 33.3,
                awayProbability: 33.3,
                available: false,
            );
        }

        $home = (float) ($context->odds['home_imp'] ?? 0);
        $draw = (float) ($context->odds['draw_imp'] ?? 0);
        $away = (float) ($context->odds['away_imp'] ?? 0);

        if ($home + $draw + $away <= 0) {
            return new ModelPrediction($this->name(), 33.3, 33.3, 33.3, false);
        }

        $probs = ProbabilityValidator::normalize(['home' => $home, 'draw' => $draw, 'away' => $away]);

        return new ModelPrediction(
            name: $this->name(),
            homeProbability: $probs['home'],
            drawProbability: $probs['draw'],
            awayProbability: $probs['away'],
        );
    }
}
