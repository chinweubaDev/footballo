<?php

namespace Tests\Unit;

use App\Services\Prediction\Markets\BttsMarket;
use App\Services\Prediction\Markets\DoubleChanceMarket;
use App\Services\Prediction\Markets\GoalsMarket;
use App\Services\Prediction\Markets\OneXTwoMarket;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class PredictionPipelineTest extends TestCase
{
    use PredictionTestHelpers;

    public function test_pipeline_produces_consistent_probabilities(): void
    {
        $result = $this->makeEnsembleResult();

        // 1X2 exhaustive within tolerance.
        $this->assertEqualsWithDelta(
            100.0,
            $result->homeProbability + $result->drawProbability + $result->awayProbability,
            0.5
        );

        foreach ([$result->homeProbability, $result->drawProbability, $result->awayProbability, $result->over15Probability, $result->over25Probability, $result->bttsProbability] as $probability) {
            $this->assertGreaterThanOrEqual(0, $probability);
            $this->assertLessThanOrEqual(100, $probability);
        }

        // Over 1.5 >= Over 2.5 by construction.
        $this->assertGreaterThanOrEqual($result->over25Probability, $result->over15Probability);

        $this->assertGreaterThan(0, $result->expectedHomeGoals);
        $this->assertGreaterThan(0, $result->expectedAwayGoals);
        $this->assertGreaterThanOrEqual(2, count($result->correctScores));
        $this->assertSame(100, $result->dataQuality);
    }

    public function test_pipeline_works_without_odds_and_api_prediction(): void
    {
        $result = $this->makeEnsembleResult([
            'odds' => ['available' => false],
            'apiPrediction' => ['available' => false],
        ]);

        $this->assertEqualsWithDelta(
            100.0,
            $result->homeProbability + $result->drawProbability + $result->awayProbability,
            0.5
        );

        $this->assertLessThan(100, $result->dataQuality);

        // Markets still produce valid selections.
        $oneXTwo = (new OneXTwoMarket())->calculate($result);
        $this->assertContains($oneXTwo['selection'], ['home', 'draw', 'away']);

        $btts = (new BttsMarket())->calculate($result);
        $this->assertContains($btts['selection'], ['yes', 'no']);

        $dc = (new DoubleChanceMarket())->calculate($result);
        $this->assertContains($dc['selection'], ['1x', 'x2', '12']);

        $over = (new GoalsMarket('over_2_5', 2.5))->calculate($result);
        $this->assertContains($over['selection'], ['over_2_5', 'under_2_5']);
    }
}
