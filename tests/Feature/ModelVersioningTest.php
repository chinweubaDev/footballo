<?php

namespace Tests\Feature;

use App\Models\PredictionModel;
use Database\Seeders\PredictionModelSeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class ModelVersioningTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionModelSeeder::class);
    }

    public function test_v1_0_0_is_preserved_and_active(): void
    {
        $v100 = PredictionModel::where('version', 'v1.0.0')->first();

        $this->assertNotNull($v100);
        $this->assertTrue((bool) $v100->active);
    }

    public function test_v1_1_0_is_shadow_not_active(): void
    {
        $v110 = PredictionModel::where('version', 'v1.1.0')->first();

        $this->assertNotNull($v110);
        $this->assertFalse((bool) $v110->active);
        $this->assertSame(PredictionModel::STATUS_SHADOW, $v110->status);
    }

    public function test_exactly_one_active_model(): void
    {
        $this->assertSame(1, PredictionModel::where('active', true)->count());
    }

    public function test_seeding_again_does_not_deactivate_an_activated_v1_1_0(): void
    {
        // Admin activates v1.1.0 explicitly.
        PredictionModel::where('version', 'v1.1.0')->update(['active' => true]);
        PredictionModel::where('version', 'v1.0.0')->update(['active' => false]);

        // Re-seeding must NOT silently flip v1.0.0 back on.
        $this->seed(PredictionModelSeeder::class);

        $this->assertTrue((bool) PredictionModel::where('version', 'v1.1.0')->first()->active);
    }
}
