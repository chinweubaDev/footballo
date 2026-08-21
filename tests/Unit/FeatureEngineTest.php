<?php

namespace Tests\Unit;

use App\Services\Prediction\FeatureEngine;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class FeatureEngineTest extends TestCase
{
    use PredictionTestHelpers;

    public function test_full_context_gives_maximum_data_quality(): void
    {
        $features = (new FeatureEngine())->build($this->makeContext());

        $this->assertSame(100, $features['data_quality']);
    }

    public function test_missing_optional_data_reduces_data_quality(): void
    {
        $context = $this->makeContext([
            'odds' => ['available' => false],
            'apiPrediction' => ['available' => false],
            'h2h' => ['matches' => 0],
        ]);

        $features = (new FeatureEngine())->build($context);

        $this->assertLessThan(100, $features['data_quality']);
        $this->assertGreaterThanOrEqual(60, $features['data_quality']);
    }

    public function test_features_are_deterministic(): void
    {
        $a = (new FeatureEngine())->build($this->makeContext());
        $b = (new FeatureEngine())->build($this->makeContext());

        $this->assertSame($a, $b);
    }

    public function test_strengths_stay_within_bounds(): void
    {
        $features = (new FeatureEngine())->build($this->makeContext());

        foreach (['home_attack_strength', 'away_attack_strength', 'home_defense_strength', 'away_defense_strength'] as $key) {
            $this->assertGreaterThanOrEqual(0.5, $features[$key]);
            $this->assertLessThanOrEqual(2.0, $features[$key]);
        }
    }
}
