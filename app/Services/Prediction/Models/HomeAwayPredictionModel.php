<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class HomeAwayPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'home_away';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        // Compare the home team's home form with the away team's away form.
        $diff = $features['home_home_form_score'] - $features['away_away_form_score'];

        $home = ProbabilityValidator::clamp(42.0 + $diff * 0.35, 5.0, 85.0);
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
