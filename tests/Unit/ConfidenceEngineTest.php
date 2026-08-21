<?php

namespace Tests\Unit;

use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\Markets\OneXTwoMarket;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class ConfidenceEngineTest extends TestCase
{
    use PredictionTestHelpers;

    public function test_high_probability_and_agreement_gives_high_confidence(): void
    {
        $context = $this->makeContext();
        $result = $this->makeResult(2.5, 0.7);
        $result->modelAgreement = 95.0;
        $result->dataQuality = 95;

        $oneXTwo = (new OneXTwoMarket())->calculate($result);
        $confidence = (new ConfidenceEngine())->calculate($context, '1x2', $oneXTwo['selection'], $oneXTwo['probability'], $result);

        $this->assertGreaterThanOrEqual(65, $confidence['score']);
        $this->assertContains($confidence['level'], ['high', 'very_high']);
    }

    public function test_low_agreement_reduces_confidence(): void
    {
        $context = $this->makeContext();

        $highAgreement = $this->makeResult(2.0, 1.0);
        $highAgreement->modelAgreement = 90.0;

        $lowAgreement = $this->makeResult(2.0, 1.0);
        $lowAgreement->modelAgreement = 20.0;

        $engine = new ConfidenceEngine();

        $a = $engine->calculate($context, '1x2', 'home', 70.0, $highAgreement);
        $b = $engine->calculate($context, '1x2', 'home', 70.0, $lowAgreement);

        $this->assertGreaterThan($b['score'], $a['score']);
    }

    public function test_poor_data_prevents_false_confidence(): void
    {
        $context = $this->makeContext();
        $result = $this->makeResult(3.0, 0.3);
        $result->homeProbability = 90.0;
        $result->drawProbability = 6.0;
        $result->awayProbability = 4.0;
        $result->modelAgreement = 100.0;
        $result->dataQuality = 30;

        $confidence = (new ConfidenceEngine())->calculate($context, '1x2', 'home', 90.0, $result);

        $this->assertLessThan(80, $confidence['score']);
        $this->assertNotSame('very_high', $confidence['level']);
    }
}
