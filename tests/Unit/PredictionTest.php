<?php

namespace Tests\Unit;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionFeature;
use App\Models\PredictionModel;
use App\Models\PredictionOverride;
use App\Models\User;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_new_fields_persist(): void
    {
        $model = PredictionModel::create(['name' => 'Ensemble', 'version' => 'v1.0.0', 'active' => true]);

        $prediction = Prediction::create([
            'market_code' => '1x2',
            'selection' => 'home',
            'probability' => 78.50,
            'model_version' => 'v1.0.0',
            'model_id' => $model->id,
            'featured' => true,
            'featured_priority' => 3,
            'admin_featured' => true,
            'data_quality_score' => 88,
            'published_at' => now(),
        ]);

        $this->assertSame('1x2', $prediction->market_code);
        $this->assertSame('home', $prediction->selection);
        $this->assertTrue($prediction->featured);
        $this->assertSame(3, $prediction->featured_priority);
        $this->assertSame(88, $prediction->data_quality_score);
        $this->assertNotNull($prediction->published_at);
    }

    public function test_legacy_fields_remain_functional(): void
    {
        $prediction = Prediction::create([
            'category' => '1x2',
            'tip' => 'Home Win (1)',
            'confidence' => 82,
            'odds' => 1.95,
            'analysis' => 'Sample analysis',
            'status' => 'pending',
        ]);

        $this->assertSame('1x2', $prediction->category);
        $this->assertSame('Home Win (1)', $prediction->tip);
        $this->assertSame(82, $prediction->confidence);
        $this->assertSame('pending', $prediction->status);
    }

    public function test_probability_and_datetime_casts_work(): void
    {
        $prediction = Prediction::create([
            'probability' => 78.50,
            'featured_until' => '2026-08-25 18:00:00',
            'locked_at' => '2026-08-24 12:00:00',
            'overridden_at' => '2026-08-23 09:30:00',
        ]);

        $this->assertSame('78.50', $prediction->probability);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $prediction->featured_until);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $prediction->locked_at);
    }

    public function test_relationships(): void
    {
        $league = League::create(['api_football_league_id' => 39, 'name' => 'Premier League', 'slug' => 'premier-league', 'enabled' => true]);
        $model = PredictionModel::create(['name' => 'Ensemble', 'version' => 'v1.0.0', 'active' => true]);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);

        $prediction = Prediction::create([
            'market_code' => '1x2',
            'league_id' => 39,
            'model_id' => $model->id,
            'overridden_by' => $admin->id,
        ]);

        PredictionOverride::create([
            'prediction_id' => $prediction->id,
            'original_selection' => 'home',
            'new_selection' => 'draw',
            'reason' => 'Key striker unavailable',
            'admin_id' => $admin->id,
        ]);

        PredictionFeature::create([
            'prediction_id' => $prediction->id,
            'fixture_id' => null,
            'model_version' => 'v1.0.0',
            'features' => ['xG' => 2.1],
        ]);

        $this->assertSame('Premier League', $prediction->league->name);
        $this->assertSame('v1.0.0', $prediction->model->version);
        $this->assertSame('Admin', $prediction->overriddenBy->name);
        $this->assertSame(1, $prediction->overrides()->count());
        $this->assertSame(1, $prediction->features()->count());
    }

    public function test_scopes(): void
    {
        Prediction::create(['market_code' => '1x2', 'status' => 'published', 'featured' => true, 'league_id' => 39]);
        Prediction::create(['market_code' => 'over_2_5', 'status' => 'pending', 'featured' => false, 'league_id' => 140]);

        $this->assertSame(1, Prediction::published()->count());
        $this->assertSame(1, Prediction::featured()->count());
        $this->assertSame(1, Prediction::surePick()->count());
        $this->assertSame(1, Prediction::byMarket('over_2_5')->count());
        $this->assertSame(1, Prediction::byLeague(140)->count());
    }
}
