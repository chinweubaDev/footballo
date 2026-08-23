<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\FeatureProvenanceService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class FeatureProvenanceServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function makePrediction(array $overrides = []): Prediction
    {
        $fixture = Fixture::create([
            'api_fixture_id' => 1,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addHours(2),
        ]);

        return Prediction::create(array_merge([
            'fixture_id' => $fixture->id,
            'market_code' => '1x2',
            'selection' => 'home',
            'probability' => 60,
            'confidence' => 70,
            'model_version' => 'v1.0.0',
            'prediction_generated_at' => now()->subHours(1),
            'feature_data_timestamp' => now()->subHours(1)->subMinutes(10),
        ], $overrides));
    }

    public function test_valid_provenance(): void
    {
        $check = (new FeatureProvenanceService())->check($this->makePrediction());

        $this->assertSame('valid', $check['status']);
    }

    public function test_invalid_when_feature_after_generation(): void
    {
        $prediction = $this->makePrediction([
            'feature_data_timestamp' => now(), // after generated_at (1h ago)
        ]);

        $check = (new FeatureProvenanceService())->check($prediction);

        $this->assertSame('invalid', $check['status']);
    }

    public function test_uncertain_when_timestamps_missing(): void
    {
        $prediction = $this->makePrediction([
            'prediction_generated_at' => null,
            'feature_data_timestamp' => null,
        ]);

        $check = (new FeatureProvenanceService())->check($prediction);

        $this->assertSame('provenance_uncertain', $check['status']);
    }
}
