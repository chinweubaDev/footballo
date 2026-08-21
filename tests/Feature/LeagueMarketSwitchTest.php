<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use App\Services\Prediction\DataCollector;
use Database\Seeders\PredictionCategorySeeder;
use Mockery;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

/**
 * League and market enable/disable switches: disabled leagues/markets must
 * never publish publicly.
 */
class LeagueMarketSwitchTest extends TestCase
{
    use InteractsWithPredictionSchema;
    use PredictionTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        PredictionModel::create([
            'name' => 'v1.0.0',
            'version' => 'v1.0.0',
            'active' => true,
            'status' => PredictionModel::STATUS_ACTIVE,
        ]);
    }

    protected function engine(): \App\Services\Prediction\PredictionEngine
    {
        $collector = Mockery::mock(DataCollector::class);
        $collector->shouldReceive('collect')->andReturn($this->makeContext());

        return $this->makePredictionEngine($collector);
    }

    public function test_disabled_league_does_not_publish(): void
    {
        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => false, // disabled
            'prediction_enabled' => true,
            'auto_publish' => true,
            'prediction_min_confidence' => 0,
        ]);

        $fixture = Fixture::create([
            'api_fixture_id' => 7701,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'match_date' => now()->addDay(),
        ]);

        $this->engine()->generate($fixture);

        $this->assertSame(0, Prediction::where('fixture_id', $fixture->id)->where('status', 'published')->count());
    }

    public function test_enabled_league_publishes_eligible_markets(): void
    {
        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'auto_publish' => true,
            'prediction_min_confidence' => 0,
        ]);

        $fixture = Fixture::create([
            'api_fixture_id' => 7702,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'match_date' => now()->addDay(),
        ]);

        $this->engine()->generate($fixture);

        $this->assertGreaterThan(0, Prediction::where('fixture_id', $fixture->id)->where('status', 'published')->count());
    }

    public function test_disabled_market_is_not_generated(): void
    {
        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'auto_publish' => true,
            'prediction_min_confidence' => 0,
        ]);

        PredictionCategory::where('code', 'correct_score')->update(['enabled' => false]);

        $fixture = Fixture::create([
            'api_fixture_id' => 7703,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'match_date' => now()->addDay(),
        ]);

        $this->engine()->generate($fixture);

        // Correct Score is disabled -> no row generated.
        $this->assertSame(0, Prediction::where('fixture_id', $fixture->id)->where('market_code', 'correct_score')->count());
        // Other markets still generated.
        $this->assertGreaterThan(0, Prediction::where('fixture_id', $fixture->id)->where('market_code', 'over_1_5')->count());
    }
}
