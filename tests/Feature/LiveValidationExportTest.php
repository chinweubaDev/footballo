<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\User;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1M §29 — live validation audit export.
 */
class LiveValidationExportTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_export_streams_audit_dataset_csv(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);

        $league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
        ]);

        $fixture = Fixture::create([
            'api_fixture_id' => 777,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'status' => 'FT',
            'home_goals' => 2,
            'away_goals' => 1,
            'match_date' => now()->subDay(),
        ]);

        Prediction::create([
            'fixture_id' => $fixture->id,
            'league_id' => 39,
            'market_code' => '1x2',
            'selection' => 'home',
            'raw_probability' => 70,
            'calibrated_probability' => 72,
            'probability' => 72,
            'confidence' => 80,
            'model_version' => 'v1.0.0',
            'status' => 'published',
            'result' => 'won',
            'model_result' => 'won',
            'public_result' => 'won',
            'settlement_result' => 'won',
            'provenance_status' => 'valid',
            'actual_score' => '2-1',
            'settled_at' => now(),
            'prediction_generated_at' => now()->subDays(2),
            'feature_data_timestamp' => now()->subDays(2)->subHour(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.predictions.performance.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('fixture_id', $content);
        $this->assertStringContainsString('home_team', $content);
        $this->assertStringContainsString('Arsenal', $content);
        $this->assertStringContainsString('1x2', $content);
        $this->assertStringContainsString('v1.0.0', $content);
        $this->assertStringContainsString('raw_probability', $content);
        $this->assertStringContainsString('calibrated_probability', $content);
        $this->assertStringContainsString('provenance_status', $content);
        $this->assertStringContainsString('model_result', $content);
        $this->assertStringContainsString('override_result', $content);
        $this->assertStringContainsString('public_result', $content);
        $this->assertStringContainsString('settlement_result', $content);
        $this->assertStringContainsString('valid', $content);
    }

    public function test_non_admin_cannot_download_export(): void
    {
        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => 'secret', 'is_admin' => false]);

        $this->actingAs($user)->get(route('admin.predictions.performance.export'))->assertForbidden();
    }

    public function test_live_validation_dashboards_render(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.predictions.live-validation'))->assertOk();
        $this->actingAs($admin)->get(route('admin.predictions.live-validation.report'))->assertOk();
        $this->actingAs($admin)->get(route('admin.predictions.performance.markets'))->assertOk();
        $this->actingAs($admin)->get(route('admin.predictions.performance.leagues'))->assertOk();
        $this->actingAs($admin)->get(route('admin.predictions.performance.matrix'))->assertOk();
        $this->actingAs($admin)->get(route('admin.predictions.validation.multi-season'))->assertOk();
        $this->actingAs($admin)->get(route('admin.system.pipeline'))->assertOk();
        $this->actingAs($admin)->get(route('admin.system.queue'))->assertOk();
        $this->actingAs($admin)->get(route('admin.system.api'))->assertOk();
        $this->actingAs($admin)->get(route('admin.system.alerts'))->assertOk();
    }
}
