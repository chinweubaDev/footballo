<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Services\Prediction\Evaluation\BacktestDataCollector;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1P §2/§3 — cross-season leakage guard.
 *
 * A season-scoped backtest (e.g. 2023) must never draw form/team stats from a
 * different season's fixtures (e.g. 2024). This complements the
 * chronological walk-forward leakage test.
 */
class SeasonIsolationTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function makeFixture(int $apiId, int $homeTeamId, int $awayTeamId, string $date, int $homeGoals, int $awayGoals, int $season): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => $apiId,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => "Team {$homeTeamId}",
            'away_team' => "Team {$awayTeamId}",
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'status' => 'FT',
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'season' => $season,
            'match_date' => Carbon::parse($date.' 15:00:00'),
        ]);
    }

    public function test_season_scoped_backtest_excludes_other_seasons(): void
    {
        // Team 1 plays in 2023 (won 5-0) and 2024 (won 7-0).
        $this->makeFixture(8001, 1, 2, '2023-01-01', 5, 0, 2023);
        $this->makeFixture(8002, 1, 3, '2024-01-01', 7, 0, 2024);

        $collector = new BacktestDataCollector();
        $collector->warm(39, 2023); // 2023 backtest scope

        // A later 2023 fixture for team 1 — form must only see the 2023 match.
        $fixture = $this->makeFixture(8003, 1, 4, '2023-03-01', 6, 0, 2023);
        $context = $collector->collect($fixture);

        $this->assertCount(1, $context->homeForm);
        $this->assertSame(5, $context->homeForm[0]['goals_for']);
        $this->assertNotContains(7, array_column($context->homeForm, 'goals_for'));
    }

    public function test_fixture_own_season_scopes_collection_automatically(): void
    {
        // 2024 fixture for team 9; a 2023 fixture for the same team exists but
        // must not leak into the 2024 fixture's form.
        $this->makeFixture(8101, 9, 2, '2023-11-01', 1, 0, 2023);
        $this->makeFixture(8102, 9, 3, '2024-01-10', 2, 0, 2024);

        $collector = new BacktestDataCollector();

        $fixture = Fixture::where('api_fixture_id', 8102)->first();
        $context = $collector->collect($fixture); // lazily warms with season 2024

        $this->assertCount(0, $context->homeForm); // no 2024 history before Jan 10
    }
}
