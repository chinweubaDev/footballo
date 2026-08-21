<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PoissonMatrix;
use App\Services\Prediction\Support\PredictionContext;

class TeamStrengthPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'team_strength';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        // Pure attack/defence strength view (no home advantage, no form).
        $lambdaHome = $features['league_home_goals'] * $features['home_attack_strength'] * $features['away_defense_strength'];
        $lambdaAway = $features['league_away_goals'] * $features['away_attack_strength'] * $features['home_defense_strength'];

        $lambdaHome = max(0.05, $lambdaHome);
        $lambdaAway = max(0.05, $lambdaAway);

        $matrix = PoissonMatrix::build($lambdaHome, $lambdaAway, (int) config('prediction.poisson.max_goals', 6));
        $oneXTwo = PoissonMatrix::oneXTwo($matrix);

        return new ModelPrediction(
            name: $this->name(),
            homeProbability: $oneXTwo['home'],
            drawProbability: $oneXTwo['draw'],
            awayProbability: $oneXTwo['away'],
            expectedHomeGoals: round($lambdaHome, 2),
            expectedAwayGoals: round($lambdaAway, 2),
        );
    }
}
