<?php

namespace App\Services\Prediction\Models;

use App\Services\Prediction\Contracts\PredictionModelInterface;
use App\Services\Prediction\Support\EnsembleResult;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PoissonMatrix;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

class EnsemblePredictionModel
{
    /** @var array<string,PredictionModelInterface> */
    protected array $models;

    public function __construct(
        PoissonPredictionModel $poisson,
        FormPredictionModel $form,
        HomeAwayPredictionModel $homeAway,
        TeamStrengthPredictionModel $teamStrength,
        ApiFootballPredictionModel $apiFootball,
        OddsPredictionModel $odds,
    ) {
        $this->models = [
            'poisson' => $poisson,
            'form' => $form,
            'home_away' => $homeAway,
            'team_strength' => $teamStrength,
            'api_football' => $apiFootball,
            'odds' => $odds,
        ];
    }

    public function predict(PredictionContext $context, array $features, ?array $weights = null): EnsembleResult
    {
        $weights = $this->resolveWeights($weights);

        /** @var list<ModelPrediction> $available */
        $available = [];
        $all = [];

        foreach ($this->models as $key => $model) {
            $prediction = $model->predict($context, $features);
            $prediction->weight = $weights[$key] ?? 0.0;
            $all[] = $prediction;

            if ($prediction->available) {
                $available[] = $prediction;
            }
        }

        if (empty($available)) {
            throw new \RuntimeException('No prediction model produced a result for fixture #'.$context->fixture->id);
        }

        $oneXTwo = $this->blendOneXTwo($available);
        $expected = $this->expectedGoals($available, $features);

        $maxGoals = (int) config('prediction.poisson.max_goals', 6);
        $matrix = PoissonMatrix::build($expected['home'], $expected['away'], $maxGoals);

        $doubleChance = [
            '1x' => round($oneXTwo['home'] + $oneXTwo['draw'], 2),
            'x2' => round($oneXTwo['draw'] + $oneXTwo['away'], 2),
            '12' => round($oneXTwo['home'] + $oneXTwo['away'], 2),
        ];

        return new EnsembleResult(
            homeProbability: $oneXTwo['home'],
            drawProbability: $oneXTwo['draw'],
            awayProbability: $oneXTwo['away'],
            expectedHomeGoals: $expected['home'],
            expectedAwayGoals: $expected['away'],
            scoreMatrix: $matrix,
            over15Probability: PoissonMatrix::over($matrix, 1.5),
            over25Probability: PoissonMatrix::over($matrix, 2.5),
            bttsProbability: PoissonMatrix::btts($matrix),
            doubleChance: $doubleChance,
            correctScores: PoissonMatrix::topScores($matrix, 5),
            modelAgreement: $this->modelAgreement($available, $oneXTwo),
            modelPredictions: $all,
            dataQuality: (int) $features['data_quality'],
            features: $features,
        );
    }

    /**
     * @param list<ModelPrediction> $models
     * @return array{home:float,draw:float,away:float}
     */
    protected function blendOneXTwo(array $models): array
    {
        $weightSum = array_sum(array_map(fn ($m) => $m->weight, $models));

        if ($weightSum <= 0) {
            throw new \RuntimeException('Total model weight is zero.');
        }

        $home = 0.0;
        $draw = 0.0;
        $away = 0.0;

        foreach ($models as $model) {
            $w = $model->weight / $weightSum;
            $home += $model->homeProbability * $w;
            $draw += $model->drawProbability * $w;
            $away += $model->awayProbability * $w;
        }

        $normalized = ProbabilityValidator::normalize(['home' => $home, 'draw' => $draw, 'away' => $away]);

        return $normalized;
    }

    /**
     * @return array{home:float,away:float}
     */
    protected function expectedGoals(array $models, array $features): array
    {
        // Expected goals come from the Poisson model (the goal-distribution source).
        foreach ($models as $model) {
            if ($model->name === 'poisson' && $model->expectedHomeGoals !== null) {
                return [
                    'home' => max(0.05, $model->expectedHomeGoals),
                    'away' => max(0.05, $model->expectedAwayGoals ?? 0.0),
                ];
            }
        }

        return [
            'home' => max(0.05, (float) $features['league_home_goals']),
            'away' => max(0.05, (float) $features['league_away_goals']),
        ];
    }

    /**
     * Weighted agreement across models on the ensemble's top 1X2 pick.
     *
     * @param list<ModelPrediction> $models
     * @param array{home:float,draw:float,away:float} $oneXTwo
     */
    protected function modelAgreement(array $models, array $oneXTwo): float
    {
        $top = array_search(max($oneXTwo), $oneXTwo, true) ?: 'home';

        $agreeWeight = 0.0;
        $totalWeight = 0.0;

        foreach ($models as $model) {
            $totalWeight += $model->weight;

            if ($model->topPick() === $top) {
                $agreeWeight += $model->weight;
            }
        }

        return $totalWeight > 0 ? round(($agreeWeight / $totalWeight) * 100, 2) : 50.0;
    }

    protected function resolveWeights(?array $weights): array
    {
        $weights = $weights ?: config('prediction.ensemble.weights', []);

        $sum = array_sum($weights);

        if (abs($sum - 1.0) > 0.001) {
            throw new \InvalidArgumentException('Ensemble model weights must sum to 1.0, got '.$sum);
        }

        return $weights;
    }
}
