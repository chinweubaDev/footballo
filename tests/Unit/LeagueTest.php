<?php

namespace Tests\Unit;

use App\Models\League;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class LeagueTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_league_fillable_and_casts_work(): void
    {
        $league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'country' => 'England',
            'season' => 2025,
            'enabled' => true,
            'prediction_enabled' => true,
            'homepage_enabled' => true,
            'priority' => 6,
            'prediction_min_confidence' => 80,
            'auto_publish' => true,
        ]);

        $this->assertTrue($league->enabled);
        $this->assertTrue($league->auto_publish);
        $this->assertSame(80, $league->prediction_min_confidence);
        $this->assertSame(2025, $league->season);
    }

    public function test_league_scopes(): void
    {
        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'homepage_enabled' => true,
        ]);

        League::create([
            'api_football_league_id' => 140,
            'name' => 'La Liga',
            'slug' => 'la-liga',
            'enabled' => false,
            'prediction_enabled' => true,
            'homepage_enabled' => true,
        ]);

        $this->assertSame(1, League::enabled()->count());
        $this->assertSame(1, League::predictionEnabled()->count());
        $this->assertSame(1, League::homepageEnabled()->count());
    }

    public function test_league_fixtures_relationship(): void
    {
        $league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
        ]);

        \App\Models\Fixture::create([
            'api_fixture_id' => 1001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
        ]);

        $this->assertSame(1, $league->fixtures()->count());
        $this->assertSame('Arsenal', $league->fixtures()->first()->home_team);
    }
}
