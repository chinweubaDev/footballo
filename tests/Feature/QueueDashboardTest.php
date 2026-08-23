<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1N §5/§6 — queue health dashboard and failed-job management.
 */
class QueueDashboardTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_admin_can_view_queue_dashboard(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.system.queue'))->assertOk();
    }

    public function test_non_admin_is_forbidden_from_queue_dashboard(): void
    {
        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => 'secret', 'is_admin' => false]);

        $this->actingAs($user)->get(route('admin.system.queue'))->assertForbidden();
    }

    public function test_guest_is_redirected_from_queue_dashboard(): void
    {
        $this->get(route('admin.system.queue'))->assertRedirect(route('login'));
    }
}
