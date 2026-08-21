<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\User;
use App\Policies\PredictionPolicy;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionAuthorizationTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_policy_grants_admin_only(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);
        $regular = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => 'secret', 'is_admin' => false]);

        $prediction = Prediction::create([
            'market_code' => '1x2',
            'selection' => 'home',
            'model_version' => 'v1.0.0',
        ]);

        $policy = new PredictionPolicy();

        $this->assertTrue($policy->override($admin, $prediction));
        $this->assertTrue($policy->publish($admin, $prediction));
        $this->assertTrue($policy->lock($admin, $prediction));

        $this->assertFalse($policy->override($regular, $prediction));
        $this->assertFalse($policy->publish($regular, $prediction));
        $this->assertFalse($policy->lock($regular, $prediction));
    }

    public function test_non_admin_is_forbidden_from_admin_panel(): void
    {
        $nonAdmin = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => 'secret', 'is_admin' => false]);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);

        $this->actingAs($nonAdmin)->get(route('admin.predictions'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.predictions'))->assertOk();
    }
}
