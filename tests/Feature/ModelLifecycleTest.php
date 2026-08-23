<?php

namespace Tests\Feature;

use App\Models\ModelAuditLog;
use App\Models\PredictionModel;
use App\Models\User;
use App\Services\Prediction\Validation\ModelLifecycleService;
use Database\Seeders\PredictionModelSeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class ModelLifecycleTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected User $admin;
    protected ModelLifecycleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionModelSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret',
            'is_admin' => true,
        ]);

        $this->service = new ModelLifecycleService();
    }

    public function test_activation_is_rejected_without_approval(): void
    {
        $shadow = PredictionModel::where('version', 'v1.1.0')->first();

        $this->expectException(\DomainException::class);

        $this->service->activate($shadow, $this->admin, 'trying to skip approval');
    }

    public function test_activation_requires_sufficient_shadow_sample(): void
    {
        config()->set('evaluation.model_gate.minimum_shadow_predictions', 500);

        $shadow = PredictionModel::where('version', 'v1.1.0')->first();
        $this->service->approve($shadow, $this->admin);

        // Approved but zero shadow predictions -> still rejected.
        $this->expectException(\DomainException::class);

        $this->service->activate($shadow->fresh(), $this->admin);
    }

    public function test_approve_then_activate_retires_previous_active(): void
    {
        config()->set('evaluation.model_gate.minimum_shadow_predictions', 0);

        $shadow = PredictionModel::where('version', 'v1.1.0')->first();
        $v100 = PredictionModel::where('version', 'v1.0.0')->first();

        $this->service->approve($shadow, $this->admin, 'validated on held-out data');
        $this->assertSame(PredictionModel::STATUS_APPROVED, $shadow->fresh()->status);

        $this->service->activate($shadow->fresh(), $this->admin, 'production promotion');

        // Exactly one ACTIVE, and it is v1.1.0.
        $this->assertSame(1, PredictionModel::where('active', true)->count());
        $this->assertSame('v1.1.0', PredictionModel::where('active', true)->first()->version);
        $this->assertSame(PredictionModel::STATUS_ACTIVE, $shadow->fresh()->status);

        // v1.0.0 retired.
        $this->assertSame(PredictionModel::STATUS_RETIRED, $v100->fresh()->status);
        $this->assertFalse((bool) $v100->fresh()->active);
    }

    public function test_rollback_restores_previous_baseline_without_shadow_gate(): void
    {
        config()->set('evaluation.model_gate.minimum_shadow_predictions', 500);

        $shadow = PredictionModel::where('version', 'v1.1.0')->first();
        $v100 = PredictionModel::where('version', 'v1.0.0')->first();

        // Promote v1.1.0 first (bypassing gate via rollback target setup).
        $this->service->approve($shadow, $this->admin, 'promote');
        config()->set('evaluation.model_gate.minimum_shadow_predictions', 0);
        $this->service->activate($shadow->fresh(), $this->admin, 'promote');
        $this->assertSame('v1.1.0', PredictionModel::where('active', true)->first()->version);

        // Rollback to v1.0.0 — bypasses the promotion shadow-sample gate.
        $this->service->rollback($v100->fresh(), $this->admin, 'regression detected');

        $this->assertSame('v1.0.0', PredictionModel::where('active', true)->first()->version);
        $this->assertSame(PredictionModel::STATUS_ACTIVE, $v100->fresh()->status);
        $this->assertSame(PredictionModel::STATUS_RETIRED, $shadow->fresh()->status);

        $this->assertTrue(ModelAuditLog::where('prediction_model_id', $v100->id)->where('action', 'rollback')->exists());
    }

    public function test_lifecycle_transitions_are_audited(): void
    {
        config()->set('evaluation.model_gate.minimum_shadow_predictions', 0);

        $shadow = PredictionModel::where('version', 'v1.1.0')->first();
        $this->service->approve($shadow, $this->admin);
        $this->service->activate($shadow->fresh(), $this->admin);

        $logs = ModelAuditLog::where('prediction_model_id', $shadow->id)->get();

        $actions = $logs->pluck('action')->all();
        $this->assertContains('approved', $actions);
        $this->assertContains('activated', $actions);

        // The previous active model's retirement is also recorded.
        $v100 = PredictionModel::where('version', 'v1.0.0')->first();
        $this->assertContains('retired', ModelAuditLog::where('prediction_model_id', $v100->id)->pluck('action')->all());
    }
}
