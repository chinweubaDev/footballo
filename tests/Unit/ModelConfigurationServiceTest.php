<?php

namespace Tests\Unit;

use App\Models\PredictionModel;
use App\Services\Prediction\Calibration\ModelConfigurationService;
use App\Services\Prediction\Calibration\ProbabilityCalibrator;
use Tests\TestCase;

class ModelConfigurationServiceTest extends TestCase
{
    protected function service(): ModelConfigurationService
    {
        return new ModelConfigurationService();
    }

    public function test_v1_0_0_has_no_calibration_and_global_weights(): void
    {
        $model = new PredictionModel([
            'version' => 'v1.0.0',
            'configuration' => null,
        ]);

        $service = $this->service();

        $this->assertFalse($service->hasCalibration($model));
        $this->assertSame([], $service->calibrators($model));
        $this->assertNull($service->calibrationVersion($model));
        $this->assertSame(config('prediction.ensemble.weights'), $service->resolveWeights($model));
    }

    public function test_v1_1_0_builds_calibrators_from_configuration(): void
    {
        $params = [
            'method' => 'platt',
            'a' => 0.4,
            'b' => -0.2,
            'isotonic' => [],
        ];

        $model = new PredictionModel([
            'version' => 'v1.1.0',
            'configuration' => [
                'calibration' => [
                    'over_1_5' => $params,
                    'btts' => $params,
                ],
                'calibration_meta' => ['calibrated_at' => '2026-08-21 16:13:56'],
            ],
        ]);

        $service = $this->service();
        $calibrators = $service->calibrators($model);

        $this->assertTrue($service->hasCalibration($model));
        $this->assertArrayHasKey('over_1_5', $calibrators);
        $this->assertArrayHasKey('btts', $calibrators);
        $this->assertInstanceOf(ProbabilityCalibrator::class, $calibrators['over_1_5']);
        $this->assertSame('2026-08-21 16:13:56', $service->calibrationVersion($model));
    }

    public function test_model_specific_weights_take_precedence(): void
    {
        $weights = ['poisson' => 0.5, 'form' => 0.5];

        $model = new PredictionModel([
            'version' => 'v1.2.0',
            'configuration' => ['weights' => $weights],
        ]);

        $this->assertSame($weights, $this->service()->resolveWeights($model));
    }

    public function test_no_silent_fallback_between_versions(): void
    {
        // A calibrated model and an uncalibrated model must resolve
        // independently — never cross-contaminated.
        $calibrated = new PredictionModel([
            'version' => 'v1.1.0',
            'configuration' => ['calibration' => ['over_1_5' => ['method' => 'platt', 'a' => 1.0, 'b' => 0.0, 'isotonic' => []]]],
        ]);

        $plain = new PredictionModel(['version' => 'v1.0.0', 'configuration' => null]);

        $service = $this->service();

        $this->assertNotEmpty($service->calibrators($calibrated));
        $this->assertEmpty($service->calibrators($plain));
    }
}
