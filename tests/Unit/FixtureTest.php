<?php

namespace Tests\Unit;

use App\Models\Fixture;
use App\Models\League;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class FixtureTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_slug_and_elapsed_persist(): void
    {
        $fixture = Fixture::create([
            'api_fixture_id' => 5001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
            'slug' => 'arsenal-vs-chelsea',
            'elapsed' => 67,
        ]);

        $this->assertSame('arsenal-vs-chelsea', $fixture->slug);
        $this->assertSame(67, $fixture->elapsed);
    }

    public function test_league_relationship_works(): void
    {
        League::create(['api_football_league_id' => 39, 'name' => 'Premier League', 'slug' => 'premier-league', 'enabled' => true]);

        $fixture = Fixture::create([
            'api_fixture_id' => 5002,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Man City',
            'away_team' => 'Liverpool',
            'match_date' => now()->addDay(),
        ]);

        $this->assertSame('Premier League', $fixture->league->name);
    }

    public function test_upcoming_scope(): void
    {
        Fixture::create([
            'api_fixture_id' => 5003,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'A',
            'away_team' => 'B',
            'match_date' => now()->addDay(),
        ]);

        Fixture::create([
            'api_fixture_id' => 5004,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'C',
            'away_team' => 'D',
            'match_date' => now()->subDay(),
        ]);

        $this->assertSame(1, Fixture::upcoming()->count());
    }
}
