<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class ApiFootballPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'api_football';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        if (! ($context->apiPrediction['available'] ?? false)) {
            return new ModelPrediction(
                name: $this->name(),
                homeProbability: 33.3,
                drawProbability: 33.3,
                awayProbability: 33.3,
                available: false,
            );
        }

        $home = (float) ($context->apiPrediction['hp'] ?? 0);
        $draw = (float) ($context->apiPrediction['dp'] ?? 0);
        $away = (float) ($context->apiPrediction['ap'] ?? 0);

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
