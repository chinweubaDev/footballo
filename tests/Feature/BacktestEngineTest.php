<?php

namespace Tests\Feature;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\Evaluation\BacktestDataCollector;
use App\Services\Prediction\Evaluation\BacktestEngine;
use App\Services\Prediction\Evaluation\MarketResultResolver;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\FeatureEngine;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class BacktestEngineTest extends TestCase
{
    use InteractsWithPredictionSchema, PredictionTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function engine(): BacktestEngine
    {
        return new BacktestEngine(
            new BacktestDataCollector(),
            new FeatureEngine(),
            $this->newEnsemble(),
            new ConfidenceEngine(),
            new MarketResultResolver(),
            new MetricsCalculator(),
        );
    }

    protected function makeRun(array $overrides = []): BacktestRun
    {
        return BacktestRun::create(array_merge([
            'league_id' => null,
            'season' => null,
            'date_start' => null,
            'date_end' => null,
            'markets' => ['1x2'],
            'min_confidence' => 0,
            'min_probability' => 0,
            'model_version' => 'v1.0.0',
            'config_snapshot' => config('prediction'),
            'status' => BacktestRun::STATUS_QUEUED,
        ], $overrides));
    }

    protected function makeCompletedFixture(array $overrides = []): Fixture
    {
        return Fixture::create(array_merge([
            'api_fixture_id' => Fixture::max('api_fixture_id') + 1,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Home Team',
            'away_team' => 'Away Team',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'status' => 'FT',
            'home_goals' => 1,
            'away_goals' => 0,
            'match_date' => Carbon::parse('2025-01-10 15:00:00'),
        ], $overrides));
    }

    public function test_backtest_processes_fixtures_and_records_results(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeCompletedFixture([
                'api_fixture_id' => 1000 + $i,
                'home_team_id' => 100 + $i,
                'away_team_id' => 200 + $i,
                'home_goals' => ($i % 2 === 0) ? 2 : 0,
                'away_goals' => ($i % 2 === 0) ? 1 : 0,
            ]);
        }

        $run = $this->makeRun();
        $metrics = $this->engine()->run($run);

        $run->refresh();

        $this->assertSame(BacktestRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(5, $run->total_fixtures);
        $this->assertSame(5, $run->processed_fixtures);
        $this->assertGreaterThan(0, $run->generated_predictions);
        $this->assertSame($run->generated_predictions, $run->resolved_predictions);

        $this->assertArrayHasKey('overview', $metrics);

        // Every prediction carries the required fields and a result.
        $predictions = BacktestPrediction::where('backtest_run_id', $run->id)->get();
        $this->assertNotEmpty($predictions);

        foreach ($predictions as $p) {
            $this->assertNotNull($p->predicted_at);
            $this->assertNotNull($p->model_version);
            $this->assertNotNull($p->probability);
            $this->assertNotNull($p->confidence);
            $this->assertNotNull($p->result);
        }
    }

    public function test_backtest_is_isolated_from_live_predictions(): void
    {
        $this->makeCompletedFixture();

        $liveBefore = Prediction::count();
        $this->assertSame(0, $liveBefore);

        $run = $this->makeRun(['markets' => ['1x2', 'over_2_5']]);
        $this->engine()->run($run);

        // Live predictions table must remain untouched.
        $this->assertSame(0, Prediction::count());
        // Backtest predictions are stored separately.
        $this->assertGreaterThan(0, BacktestPrediction::where('backtest_run_id', $run->id)->count());
    }

    public function test_backtest_is_idempotent_and_reproducible(): void
    {
        $this->makeCompletedFixture();

        $run = $this->makeRun();
        $this->engine()->run($run);
        $firstCount = BacktestPrediction::where('backtest_run_id', $run->id)->count();

        // Re-running deletes and regenerates — no duplicate rows.
        $this->engine()->run($run->fresh());
        $secondCount = BacktestPrediction::where('backtest_run_id', $run->id)->count();

        $this->assertSame($firstCount, $secondCount);
    }

    public function test_future_fixtures_are_excluded(): void
    {
        $this->makeCompletedFixture(['api_fixture_id' => 2001]);

        // A not-started fixture in the future must not be backtested.
        Fixture::create([
            'api_fixture_id' => 2002,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Future Home',
            'away_team' => 'Future Away',
            'home_team_id' => 300,
            'away_team_id' => 400,
            'status' => 'NS',
            'home_goals' => null,
            'away_goals' => null,
            'match_date' => now()->addWeek(),
        ]);

        $run = $this->makeRun();
        $this->engine()->run($run);

        $this->assertSame(1, $run->fresh()->total_fixtures);
    }

    public function test_date_range_is_respected(): void
    {
        $this->makeCompletedFixture(['api_fixture_id' => 3001, 'match_date' => Carbon::parse('2025-01-05')]);
        $this->makeCompletedFixture(['api_fixture_id' => 3002, 'match_date' => Carbon::parse('2025-02-05')]);

        $run = $this->makeRun([
            'date_start' => '2025-01-01',
            'date_end' => '2025-01-31',
        ]);

        $this->engine()->run($run);

        $this->assertSame(1, $run->fresh()->total_fixtures);
    }
}
