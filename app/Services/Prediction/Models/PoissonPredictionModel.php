<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PoissonMatrix;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class PoissonPredictionModel implements PredictionModelInterface
{
    public function name(): string
    {
        return 'poisson';
    }

    public function predict(PredictionContext $context, array $features): ModelPrediction
    {
        $lambdaHome = $this->lambdaHome($features);
        $lambdaAway = $this->lambdaAway($features);

        // Blend xG when available (small weight so it never dominates).
        if (is_numeric($features['home_xg'] ?? null) && is_numeric($features['away_xg'] ?? null)) {
            $lambdaHome = 0.85 * $lambdaHome + 0.15 * (float) $features['home_xg'];
            $lambdaAway = 0.85 * $lambdaAway + 0.15 * (float) $features['away_xg'];
        }

        $lambdaHome = max(0.05, $lambdaHome);
        $lambdaAway = max(0.05, $lambdaAway);

        $maxGoals = (int) config('prediction.poisson.max_goals', 6);
        $matrix = PoissonMatrix::build($lambdaHome, $lambdaAway, $maxGoals);
        $oneXTwo = PoissonMatrix::oneXTwo($matrix);

        return new ModelPrediction(
            name: $this->name(),
            homeProbability: $oneXTwo['home'],
            drawProbability: $oneXTwo['draw'],
            awayProbability: $oneXTwo['away'],
            available: true,
            expectedHomeGoals: round($lambdaHome, 2),
            expectedAwayGoals: round($lambdaAway, 2),
        );
    }

    protected function lambdaHome(array $f): float
    {
        $base = $f['league_home_goals'] * $f['home_attack_strength'] * $f['away_defense_strength'];
        $formAdjustment = ProbabilityValidator::clamp(($f['home_form_score'] - $f['away_form_score']) * 0.004, -0.3, 0.3);

        return $base + $f['home_advantage'] + $formAdjustment;
    }

    protected function lambdaAway(array $f): float
    {
        $base = $f['league_away_goals'] * $f['away_attack_strength'] * $f['home_defense_strength'];
        $formAdjustment = ProbabilityValidator::clamp(($f['away_form_score'] - $f['home_form_score']) * 0.004, -0.3, 0.3);

        return $base + $formAdjustment;
    }
}
