<?php

namespace Tests\Feature;

use App\Models\PredictionCategory;
use App\Models\PredictionGateAudit;
use App\Models\User;
use App\Services\Prediction\Admin\MarketGateService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class MarketGateServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected User $admin;
    protected MarketGateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret',
            'is_admin' => true,
        ]);

        $this->service = new MarketGateService();
    }

    protected function category(): PredictionCategory
    {
        return PredictionCategory::create([
            'name' => 'Over 1.5',
            'slug' => 'over-1-5',
            'code' => 'over_1_5',
            'enabled' => true,
            'min_confidence' => 75,
            'min_probability' => 70,
            'minimum_sample_size' => 100,
            'gate_status' => 'none',
            'homepage_enabled' => false,
            'sort_order' => 1,
        ]);
    }

    public function test_approve_applies_thresholds_and_writes_audit(): void
    {
        $category = $this->category();

        $this->service->approve($category, $this->admin, 80, 60, 'measured improvement');

        $category->refresh();

        $this->assertSame(80, $category->min_probability);
        $this->assertSame(60, $category->min_confidence);
        $this->assertSame('approved', $category->gate_status);

        $audit = PredictionGateAudit::where('market_code', 'over_1_5')->first();

        $this->assertNotNull($audit);
        $this->assertSame('approved', $audit->action);
        $this->assertSame(70, $audit->old_probability);
        $this->assertSame(80, $audit->new_probability);
        $this->assertSame(75, $audit->old_confidence);
        $this->assertSame(60, $audit->new_confidence);
        $this->assertSame($this->admin->id, $audit->admin_id);
        $this->assertSame('measured improvement', $audit->reason);
    }

    public function test_reject_does_not_change_thresholds_but_is_audited(): void
    {
        $category = $this->category();

        $this->service->reject($category, $this->admin, 'sample too small');

        $category->refresh();

        $this->assertSame(70, $category->min_probability);
        $this->assertSame(75, $category->min_confidence);
        $this->assertSame('rejected', $category->gate_status);

        $audit = PredictionGateAudit::where('market_code', 'over_1_5')->first();

        $this->assertNotNull($audit);
        $this->assertSame('rejected', $audit->action);
        $this->assertSame(70, $audit->old_probability);
        $this->assertSame(70, $audit->new_probability);
        $this->assertSame('sample too small', $audit->reason);
    }

    public function test_every_change_is_recorded_immutably(): void
    {
        $category = $this->category();

        $this->service->reject($category, $this->admin, 'first');
        $this->service->approve($category->fresh(), $this->admin, 65, 55, 'second');

        $this->assertSame(2, PredictionGateAudit::where('market_code', 'over_1_5')->count());
    }
}
