<?php

namespace Tests\Feature;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\PredictionModel;
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

        // BacktestEngine (Phase 1H) requires the requested model version to be
        // registered — seed the default v1.0.0.
        PredictionModel::create([
            'name' => 'Esurebet Statistical Ensemble',
            'version' => 'v1.0.0',
            'configuration' => null,
            'active' => true,
            'status' => 'candidate',
        ]);
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

    public function test_model_version_integrity_fails_on_unknown_version(): void
    {
        $this->makeCompletedFixture();

        $run = $this->makeRun(['model_version' => 'v9.9.9-does-not-exist']);

        $this->engine()->run($run);

        $this->assertSame(BacktestRun::STATUS_FAILED, $run->fresh()->status);
        $this->assertStringContainsString('not registered', $run->fresh()->error);
    }

    public function test_v1_0_0_records_raw_and_calibrated_probabilities_as_identical(): void
    {
        $this->makeCompletedFixture();

        $run = $this->makeRun(['model_version' => 'v1.0.0']);
        $this->engine()->run($run);

        $predictions = BacktestPrediction::where('backtest_run_id', $run->id)->get();
        $this->assertNotEmpty($predictions);

        foreach ($predictions as $p) {
            $this->assertNotNull($p->raw_probability);
            $this->assertNotNull($p->calibrated_probability);
            $this->assertSame($p->raw_probability, $p->calibrated_probability);
            $this->assertNull($p->calibration_version);
        }
    }

    public function test_v1_1_0_walk_forward_calibration_is_applied_after_minimum_samples(): void
    {
        PredictionModel::create([
            'name' => 'Calibrated Ensemble',
            'version' => 'v1.1.0',
            'configuration' => [
                // Pre-trained params are NOT used by the walk-forward backtest;
                // their presence just marks v1.1.0 as a calibrated model.
                'calibration' => ['over_1_5' => ['method' => 'platt', 'a' => 0.15, 'b' => 0.89, 'isotonic' => []]],
            ],
        ]);

        // 31 chronological fixtures of the single over_1_5 market. Fixture 31
        // has 30 prior resolved samples, so its calibrator gets fitted.
        for ($i = 0; $i < 31; $i++) {
            $this->makeCompletedFixture([
                'api_fixture_id' => 4000 + $i,
                'home_team_id' => 500 + $i,
                'away_team_id' => 600 + $i,
                'home_goals' => ($i % 3 === 0) ? 0 : 2,
                'away_goals' => ($i % 2 === 0) ? 1 : 1,
                'match_date' => Carbon::parse('2025-01-01')->addDays($i),
            ]);
        }

        $run = $this->makeRun(['model_version' => 'v1.1.0', 'markets' => ['over_1_5']]);
        $this->engine()->run($run);

        $this->assertSame(BacktestRun::STATUS_COMPLETED, $run->fresh()->status);

        $predictions = BacktestPrediction::where('backtest_run_id', $run->id)
            ->orderBy('predicted_at')
            ->get();

        $this->assertSame(31, $predictions->count());

        // Early fixtures: not enough history to fit a calibrator.
        $this->assertNull($predictions[0]->calibration_version);

        // Later fixtures: walk-forward calibration has been applied.
        $this->assertSame('walk-forward', $predictions->last()->calibration_version);
        $this->assertNotNull($predictions->last()->raw_probability);
        $this->assertNotNull($predictions->last()->calibrated_probability);
        // The probability the model bets with is the CALIBRATED probability.
        $this->assertSame($predictions->last()->calibrated_probability, $predictions->last()->probability);
    }
}
