<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Services\Prediction\Evaluation\BacktestDataCollector;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * MANDATORY data-leakage test.
 *
 * Fixture A: January 1   (completed)
 * Fixture B: January 8   (completed)
 * Fixture C: January 15  (completed)
 *
 * The form used to predict B must NOT include C.
 * The form used to predict C MAY include B.
 */
class WalkForwardDataLeakageTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function makeFixture(int $apiId, int $homeTeamId, int $awayTeamId, string $date, int $homeGoals, int $awayGoals): Fixture
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
            'match_date' => Carbon::parse($date.' 15:00:00'),
        ]);
    }

    public function test_fixture_b_form_does_not_include_fixture_c(): void
    {
        // Team 1 wins all three matches.
        $a = $this->makeFixture(4001, 1, 2, '2025-01-01', 1, 0);
        $b = $this->makeFixture(4002, 1, 3, '2025-01-08', 2, 0);
        $c = $this->makeFixture(4003, 1, 4, '2025-01-15', 3, 0);

        $collector = new BacktestDataCollector();

        // Predicting B: team 1's form must only include A (strictly before Jan 8).
        $contextB = $collector->collect($b);
        $this->assertCount(1, $contextB->homeForm);
        $this->assertSame(1, $contextB->homeForm[0]['goals_for']); // A's 1-0

        // Predicting C: team 1's form may include BOTH A and B.
        $contextC = $collector->collect($c);
        $this->assertCount(2, $contextC->homeForm);
        $this->assertContains(1, array_column($contextC->homeForm, 'goals_for'));
        $this->assertContains(2, array_column($contextC->homeForm, 'goals_for'));

        // The future fixture C's scoreline must never appear in B's context.
        $this->assertNotContains(3, array_column($contextB->homeForm, 'goals_for'));
    }

    public function test_team_stats_exclude_future_matches(): void
    {
        $this->makeFixture(5001, 7, 8, '2025-01-01', 1, 0);
        $this->makeFixture(5002, 7, 9, '2025-01-08', 2, 0);
        $this->makeFixture(5003, 7, 10, '2025-01-15', 3, 0);

        $collector = new BacktestDataCollector();

        $b = Fixture::where('api_fixture_id', 5002)->first();
        $contextB = $collector->collect($b);

        // Team 7's stats for B (before Jan 8) should reflect only the Jan 1 match.
        $this->assertSame(1, $contextB->homeTeamStats['played_total']);
    }
}
