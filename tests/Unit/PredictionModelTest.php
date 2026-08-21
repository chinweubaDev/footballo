<?php

namespace Tests\Unit;

use App\Models\Prediction;
use App\Models\PredictionModel;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionModelTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_model_fillable_and_casts_work(): void
    {
        $model = PredictionModel::create([
            'name' => 'Esurebet Statistical Ensemble',
            'version' => 'v1.0.0',
            'description' => 'Initial statistical prediction model foundation.',
            'configuration' => ['weight_poisson' => 0.5],
            'active' => true,
        ]);

        $this->assertTrue($model->active);
        $this->assertSame(['weight_poisson' => 0.5], $model->configuration);
        $this->assertSame('v1.0.0', $model->version);
    }

    public function test_model_has_predictions(): void
    {
        $model = PredictionModel::create(['name' => 'Ensemble', 'version' => 'v1.0.0', 'active' => true]);

        Prediction::create([
            'category' => '1x2',
            'tip' => 'Home Win (1)',
            'confidence' => 80,
            'model_id' => $model->id,
            'model_version' => 'v1.0.0',
        ]);

        $this->assertSame(1, $model->predictions()->count());
    }

    public function test_active_scope(): void
    {
        PredictionModel::create(['name' => 'A', 'version' => 'v1.0.0', 'active' => true]);
        PredictionModel::create(['name' => 'B', 'version' => 'v1.1.0', 'active' => false]);

        $this->assertSame(1, PredictionModel::active()->count());
    }
}
