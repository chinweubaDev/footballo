<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Services\Prediction\Evaluation\DataDriftService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class DataDriftServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function makeFixture(string $date, int $homeGoals, int $awayGoals, int $id): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 5000 + $id,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Home Team',
            'away_team' => 'Away Team',
            'home_team_id' => 100 + $id,
            'away_team_id' => 200 + $id,
            'season' => 2025,
            'status' => 'FT',
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'match_date' => $date,
        ]);
    }

    public function test_insufficient_data_when_no_fixtures(): void
    {
        $report = (new DataDriftService())->detect(39);

        $this->assertTrue($report['insufficient_data'] ?? false);
        $this->assertFalse($report['drift_detected']);
        $this->assertSame([], $report['windows']);
    }

    public function test_detects_scoring_rate_drift(): void
    {
        config()->set('evaluation.data_drift', [
            'window_months' => 1,
            'minimum_fixtures' => 30,
            'drift_threshold_pct' => 5.0,
        ]);

        // Baseline month: ~1 goal per match.
        for ($i = 0; $i < 30; $i++) {
            $day = ($i % 28) + 1;
            $this->makeFixture(sprintf('2025-01-%02d 15:00:00', $day), 1, 0, $i);
        }

        // Later month: ~6 goals per match → material scoring-rate drift.
        for ($i = 0; $i < 30; $i++) {
            $day = ($i % 28) + 1;
            $this->makeFixture(sprintf('2025-02-%02d 15:00:00', $day), 3, 3, 100 + $i);
        }

        $report = (new DataDriftService())->detect(39);

        $this->assertTrue($report['drift_detected']);
        $this->assertContains('league_scoring_rate', $report['flags']);

        // Baseline is the January window with scoring rate ~1.0.
        $this->assertEqualsWithDelta(1.0, $report['baseline']['league_scoring_rate'], 0.001);
    }

    public function test_no_drift_when_distribution_stable(): void
    {
        config()->set('evaluation.data_drift', [
            'window_months' => 1,
            'minimum_fixtures' => 30,
            'drift_threshold_pct' => 5.0,
        ]);

        for ($i = 0; $i < 30; $i++) {
            $day = ($i % 28) + 1;
            $this->makeFixture(sprintf('2025-01-%02d 15:00:00', $day), 1, 1, $i);
        }

        for ($i = 0; $i < 30; $i++) {
            $day = ($i % 28) + 1;
            $this->makeFixture(sprintf('2025-02-%02d 15:00:00', $day), 1, 1, 100 + $i);
        }

        $report = (new DataDriftService())->detect(39);

        $this->assertFalse($report['drift_detected']);
    }
}
