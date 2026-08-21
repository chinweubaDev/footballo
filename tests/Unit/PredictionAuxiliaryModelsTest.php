<?php

namespace Tests\Unit;

use App\Models\PredictionFeature;
use App\Models\PredictionOverride;
use App\Models\PredictionPerformance;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionAuxiliaryModelsTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_override_casts_work(): void
    {
        $override = PredictionOverride::create([
            'original_selection' => 'home',
            'new_selection' => 'draw',
            'original_probability' => 76.50,
            'new_probability' => 28.00,
            'reason' => 'Key striker unavailable',
        ]);

        $this->assertSame('76.50', $override->original_probability);
        $this->assertSame('28.00', $override->new_probability);
    }

    public function test_feature_casts_features_as_array(): void
    {
        $feature = PredictionFeature::create([
            'model_version' => 'v1.0.0',
            'features' => ['home_xg' => 2.1, 'away_xg' => 0.9],
        ]);

        $this->assertSame(['home_xg' => 2.1, 'away_xg' => 0.9], $feature->features);
    }

    public function test_performance_casts_work(): void
    {
        $performance = PredictionPerformance::create([
            'market_code' => '1x2',
            'model_version' => 'v1.0.0',
            'period' => '30d',
            'period_start' => '2026-07-01 00:00:00',
            'period_end' => '2026-07-30 00:00:00',
            'total' => 100,
            'won' => 78,
            'lost' => 20,
            'void' => 2,
            'accuracy' => 78.4,
            'roi' => 12.5,
            'yield' => 11.2,
            'avg_confidence' => 81.3,
            'calibration_error' => 0.04,
            'calculated_at' => now(),
        ]);

        $this->assertSame(100, $performance->total);
        $this->assertSame(78.4, $performance->accuracy);
        $this->assertSame(12.5, $performance->roi);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $performance->period_start);
    }
}
