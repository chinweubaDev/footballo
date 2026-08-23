<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Models\PredictionOverride;
use App\Models\User;
use App\Services\Prediction\Admin\AuditLogger;
use App\Services\Prediction\Admin\PredictionAdminService;
use App\Services\Prediction\Admin\PredictionOverrideService;
use App\Services\Prediction\Admin\PredictionPublishingService;
use App\Services\Prediction\DataCollector;
use Database\Seeders\PredictionCategorySeeder;
use Mockery;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class PredictionAdminWorkflowTest extends TestCase
{
    use InteractsWithPredictionSchema;
    use PredictionTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        PredictionModel::updateOrCreate(
            ['version' => 'v1.0.0'],
            ['name' => 'Esurebet Statistical Ensemble', 'active' => true],
        );

        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'prediction_min_confidence' => 75,
            'auto_publish' => true,
        ]);
    }

    protected function makeFixture(): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 12345,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
        ]);
    }

    protected function makeAdmin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);
    }

    protected function generate(Fixture $fixture): void
    {
        $collector = Mockery::mock(DataCollector::class);
        $collector->shouldReceive('collect')->andReturn($this->makeContext());

        $this->makePredictionEngine($collector)->generate($fixture);
    }

    public function test_full_override_lock_revert_workflow(): void
    {
        $admin = $this->makeAdmin();
        $fixture = $this->makeFixture();

        // Generate prediction (model selection = home).
        $this->generate($fixture);

        $prediction = Prediction::where('fixture_id', $fixture->id)->where('market_code', '1x2')->first();
        $this->assertNotNull($prediction);
        $this->assertSame('home', $prediction->selection);

        // Admin overrides home -> draw.
        $overrides = new PredictionOverrideService(new AuditLogger());
        $overrides->override($prediction, 'draw', null, 'Late injury news', $admin);
        $prediction->refresh();

        $this->assertSame('home', $prediction->original_selection);
        $this->assertSame('draw', $prediction->admin_selection);
        $this->assertSame('draw', $prediction->effective_selection);
        $this->assertTrue($prediction->is_overridden);
        $this->assertSame(1, PredictionOverride::where('prediction_id', $prediction->id)->count());

        // Lock + regenerate -> prediction remains draw (not overwritten).
        $adminService = new PredictionAdminService(new AuditLogger());
        $adminService->lock($prediction, $admin);
        $this->assertNotNull($prediction->fresh()->locked_at);

        $this->generate($fixture);
        $prediction->refresh();

        $this->assertSame('draw', $prediction->admin_selection);
        $this->assertSame('draw', $prediction->effective_selection);

        // Unlock + revert -> back to AI selection.
        $adminService->unlock($prediction, $admin);
        $overrides->revert($prediction, $admin);
        $prediction->refresh();

        $this->assertNull($prediction->admin_selection);
        $this->assertSame('home', $prediction->effective_selection);

        // Audit history still contains the previous override + the revert.
        $this->assertGreaterThanOrEqual(2, PredictionOverride::where('prediction_id', $prediction->id)->count());
    }

    public function test_locked_prediction_cannot_be_overridden(): void
    {
        $admin = $this->makeAdmin();
        $fixture = $this->makeFixture();
        $this->generate($fixture);

        $prediction = Prediction::where('fixture_id', $fixture->id)->where('market_code', '1x2')->first();

        $adminService = new PredictionAdminService(new AuditLogger());
        $adminService->lock($prediction, $admin);

        $overrides = new PredictionOverrideService(new AuditLogger());

        $this->expectException(\DomainException::class);
        $overrides->override($prediction, 'draw', null, 'Late injury news', $admin);
    }

    public function test_no_bet_cannot_be_published_without_override(): void
    {
        $admin = $this->makeAdmin();
        $prediction = Prediction::create([
            'fixture_id' => $this->makeFixture()->id,
            'market_code' => '1x2',
            'category' => '1X2',
            'tip' => 'Home Win (1)',
            'selection' => 'home',
            'probability' => 55.0,
            'confidence' => 55,
            'model_version' => 'v1.0.0',
            'status' => 'no_bet',
        ]);

        $publishing = new PredictionPublishingService(new AuditLogger());

        $this->expectException(\DomainException::class);
        $publishing->publish($prediction, $admin);
    }

    public function test_correct_score_cannot_be_featured(): void
    {
        $admin = $this->makeAdmin();
        $prediction = Prediction::create([
            'fixture_id' => $this->makeFixture()->id,
            'market_code' => 'correct_score',
            'category' => 'Correct Score',
            'selection' => '2-1',
            'probability' => 13.4,
            'confidence' => 55,
            'model_version' => 'v1.0.0',
            'status' => 'published',
        ]);

        $adminService = new PredictionAdminService(new AuditLogger());

        $this->expectException(\DomainException::class);
        $adminService->feature($prediction, ['admin_featured' => true, 'featured_priority' => 1], $admin);
    }

    public function test_one_x_two_can_be_featured(): void
    {
        $admin = $this->makeAdmin();
        $prediction = Prediction::create([
            'fixture_id' => $this->makeFixture()->id,
            'market_code' => '1x2',
            'category' => '1X2',
            'selection' => 'home',
            'probability' => 78.0,
            'confidence' => 86,
            'model_version' => 'v1.0.0',
            'status' => 'published',
        ]);

        $adminService = new PredictionAdminService(new AuditLogger());
        $adminService->feature($prediction, ['admin_featured' => true, 'featured_priority' => 2], $admin);

        $prediction->refresh();

        $this->assertTrue($prediction->featured);
        $this->assertTrue($prediction->admin_featured);
        $this->assertSame(2, $prediction->featured_priority);
    }
}
