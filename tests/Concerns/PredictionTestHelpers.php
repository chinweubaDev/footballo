<?php

namespace Tests\Concerns;

use App\Models\Fixture;
use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\DataCollector;
use App\Services\Prediction\FeatureEngine;
use App\Services\Prediction\Models\ApiFootballPredictionModel;
use App\Services\Prediction\Models\EnsemblePredictionModel;
use App\Services\Prediction\Models\FormPredictionModel;
use App\Services\Prediction\Models\HomeAwayPredictionModel;
use App\Services\Prediction\Models\OddsPredictionModel;
use App\Services\Prediction\Models\PoissonPredictionModel;
use App\Services\Prediction\Models\TeamStrengthPredictionModel;
use App\Services\Prediction\PredictionEngine;
use App\Services\Prediction\Support\EnsembleResult;
use App\Services\Prediction\Support\PredictionContext;

trait PredictionTestHelpers
{
    /**
     * Build a realistic PredictionContext without touching the database or API.
     */
    protected function makeContext(array $overrides = []): PredictionContext
    {
        $fixture = Fixture::make([
            'id' => 1,
            'api_fixture_id' => 12345,
            'home_team_id' => 100,
            'away_team_id' => 200,
            'league_id' => 39,
            'season' => 2025,
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
        ]);

        $data = [
            'fixture' => $fixture,
            'homeTeamId' => 100,
            'awayTeamId' => 200,
            'leagueId' => 39,
            'season' => '2025',
            'homeForm' => [
                ['result' => 'W', 'goals_for' => 2, 'goals_against' => 0, 'is_home' => true],
                ['result' => 'W', 'goals_for' => 3, 'goals_against' => 1, 'is_home' => true],
                ['result' => 'D', 'goals_for' => 1, 'goals_against' => 1, 'is_home' => true],
                ['result' => 'W', 'goals_for' => 2, 'goals_against' => 1, 'is_home' => false],
                ['result' => 'L', 'goals_for' => 0, 'goals_against' => 2, 'is_home' => false],
            ],
            'awayForm' => [
                ['result' => 'L', 'goals_for' => 0, 'goals_against' => 2, 'is_home' => true],
                ['result' => 'D', 'goals_for' => 1, 'goals_against' => 1, 'is_home' => false],
                ['result' => 'L', 'goals_for' => 0, 'goals_against' => 1, 'is_home' => true],
                ['result' => 'W', 'goals_for' => 2, 'goals_against' => 1, 'is_home' => false],
                ['result' => 'L', 'goals_for' => 1, 'goals_against' => 3, 'is_home' => true],
            ],
            'homeHomeForm' => [
                ['result' => 'W', 'goals_for' => 2, 'goals_against' => 0, 'is_home' => true],
                ['result' => 'W', 'goals_for' => 3, 'goals_against' => 1, 'is_home' => true],
                ['result' => 'D', 'goals_for' => 1, 'goals_against' => 1, 'is_home' => true],
            ],
            'awayAwayForm' => [
                ['result' => 'D', 'goals_for' => 1, 'goals_against' => 1, 'is_home' => false],
                ['result' => 'W', 'goals_for' => 2, 'goals_against' => 1, 'is_home' => false],
            ],
            'homeTeamStats' => [
                'avg_goals_for_home' => 2.1,
                'avg_goals_against_home' => 0.8,
                'avg_goals_for_away' => 1.2,
                'avg_goals_against_away' => 1.4,
            ],
            'awayTeamStats' => [
                'avg_goals_for_away' => 1.1,
                'avg_goals_against_away' => 1.5,
                'avg_goals_for_home' => 1.0,
                'avg_goals_against_home' => 1.0,
            ],
            'h2h' => [
                'matches' => 6,
                'home_win_rate' => 50.0,
                'draw_rate' => 16.7,
                'away_win_rate' => 33.3,
                'avg_home_goals' => 1.5,
                'avg_away_goals' => 1.0,
                'over25_rate' => 50.0,
                'bts_rate' => 66.7,
            ],
            'odds' => [
                'available' => true,
                'home_odds' => 1.8,
                'draw_odds' => 3.5,
                'away_odds' => 4.2,
                'home_imp' => 52.0,
                'draw_imp' => 26.0,
                'away_imp' => 22.0,
                'over15_odds' => 1.25,
                'o15_imp' => 80.0,
                'over25_odds' => 1.9,
                'o25_imp' => 52.6,
                'bts_yes' => 1.8,
                'bts_no' => 1.95,
                'bts_imp' => 55.6,
            ],
            'apiPrediction' => [
                'available' => true,
                'hp' => 55.0,
                'dp' => 25.0,
                'ap' => 20.0,
                'advice' => 'Home',
                'winner' => 'Arsenal',
            ],
            'standings' => [
                'home_position' => 2,
                'away_position' => 8,
                'home_points' => 55,
                'away_points' => 40,
                'position_diff' => 6,
                'points_diff' => 15,
            ],
            'injuries' => [
                'fetched' => true,
                'home_missing' => 1,
                'away_missing' => 0,
                'total_missing' => 1,
                'has_injuries' => true,
            ],
        ];

        return new PredictionContext(...array_merge($data, $overrides));
    }

    protected function newEnsemble(): EnsemblePredictionModel
    {
        return new EnsemblePredictionModel(
            new PoissonPredictionModel(),
            new FormPredictionModel(),
            new HomeAwayPredictionModel(),
            new TeamStrengthPredictionModel(),
            new ApiFootballPredictionModel(),
            new OddsPredictionModel(),
        );
    }

    protected function makeEnsembleResult(array $overrides = []): EnsembleResult
    {
        $context = $this->makeContext($overrides);
        $features = (new FeatureEngine())->build($context);

        return $this->newEnsemble()->predict($context, $features);
    }

    protected function makePredictionEngine(DataCollector $collector): PredictionEngine
    {
        return new PredictionEngine(
            $collector,
            new FeatureEngine(),
            $this->newEnsemble(),
            new ConfidenceEngine(),
            new \App\Services\Prediction\Admin\MarketGate(),
        );
    }

    protected function makeResult(float $lambdaHome, float $lambdaAway): EnsembleResult
    {
        $matrix = \App\Services\Prediction\Support\PoissonMatrix::build($lambdaHome, $lambdaAway, 6);
        $oneXTwo = \App\Services\Prediction\Support\PoissonMatrix::oneXTwo($matrix);

        return new EnsembleResult(
            homeProbability: $oneXTwo['home'],
            drawProbability: $oneXTwo['draw'],
            awayProbability: $oneXTwo['away'],
            expectedHomeGoals: $lambdaHome,
            expectedAwayGoals: $lambdaAway,
            scoreMatrix: $matrix,
            over15Probability: \App\Services\Prediction\Support\PoissonMatrix::over($matrix, 1.5),
            over25Probability: \App\Services\Prediction\Support\PoissonMatrix::over($matrix, 2.5),
            bttsProbability: \App\Services\Prediction\Support\PoissonMatrix::btts($matrix),
            doubleChance: [
                '1x' => $oneXTwo['home'] + $oneXTwo['draw'],
                'x2' => $oneXTwo['draw'] + $oneXTwo['away'],
                '12' => $oneXTwo['home'] + $oneXTwo['away'],
            ],
            correctScores: \App\Services\Prediction\Support\PoissonMatrix::topScores($matrix, 5),
            modelAgreement: 80.0,
            modelPredictions: [],
            dataQuality: 90,
            features: ['home_form_score' => 70, 'away_form_score' => 55],
        );
    }
}
