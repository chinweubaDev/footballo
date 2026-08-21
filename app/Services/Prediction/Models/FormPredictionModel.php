<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class FormPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'form';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        $diff = $features['home_form_score'] - $features['away_form_score'];

        $home = ProbabilityValidator::clamp(40.0 + $diff * 0.35, 5.0, 85.0);
        $away = ProbabilityValidator::clamp(40.0 - $diff * 0.35, 5.0, 85.0);
        $draw = max(8.0, 100.0 - $home - $away);

        $probs = ProbabilityValidator::normalize(['home' => $home, 'draw' => $draw, 'away' => $away]);

        return new ModelPrediction(
            name: $this->name(),
            homeProbability: $probs['home'],
            drawProbability: $probs['draw'],
            awayProbability: $probs['away'],
        );
    }
}
