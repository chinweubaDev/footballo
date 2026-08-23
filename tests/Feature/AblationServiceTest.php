<?php

namespace Tests\Feature;

use App\Models\BacktestRun;
use App\Models\Fixture;
use App\Models\PredictionModel;
use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\Evaluation\BacktestDataCollector;
use App\Services\Prediction\Evaluation\BacktestEngine;
use App\Services\Prediction\Evaluation\MarketResultResolver;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\FeatureEngine;
use App\Services\Prediction\Validation\AblationService;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class AblationServiceTest extends TestCase
{
    use InteractsWithPredictionSchema, PredictionTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        // AblationService runs BacktestEngine, which requires a registered
        // model version (Phase 1H integrity).
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

    protected function makeCompletedFixture(int $id, int $homeGoals, int $awayGoals): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 7000 + $id,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Home Team',
            'away_team' => 'Away Team',
            'home_team_id' => 300 + $id,
            'away_team_id' => 400 + $id,
            'season' => 2025,
            'status' => 'FT',
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'match_date' => Carbon::parse('2025-01-10 15:00:00')->addDays($id),
        ]);
    }

    public function test_ablation_runs_full_model_and_each_ablatable_group(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeCompletedFixture($i, ($i % 2 === 0) ? 2 : 0, ($i % 2 === 0) ? 1 : 1);
        }

        $report = (new AblationService($this->engine()))->run(39, 2025, 'v1.0.0', ['1x2']);

        $this->assertArrayHasKey('full', $report['results']);
        $this->assertArrayHasKey('no_form', $report['results']);
        $this->assertArrayHasKey('no_h2h', $report['results']);
        $this->assertArrayHasKey('no_team_strength', $report['results']);
        $this->assertArrayHasKey('no_xg', $report['results']);

        // One stored BacktestRun per variant (full + 4 ablations) = 5.
        $this->assertSame(5, BacktestRun::count());

        foreach ($report['results'] as $result) {
            $this->assertArrayHasKey('accuracy', $result);
            $this->assertArrayHasKey('brier_score', $result);
            $this->assertArrayHasKey('calibration_error', $result);
            $this->assertArrayHasKey('coverage', $result);
            $this->assertGreaterThan(0, $result['resolved']);
        }
    }

    public function test_ablated_runs_store_config_snapshot_for_reproducibility(): void
    {
        $this->makeCompletedFixture(0, 2, 1);

        (new AblationService($this->engine()))->run(39, 2025, 'v1.0.0', ['1x2']);

        $noFormRun = BacktestRun::where('name', 'like', 'Ablation: No form%')->first();

        $this->assertNotNull($noFormRun);
        $this->assertSame(['form'], $noFormRun->config_snapshot['ablations']);
    }

    public function test_feature_engine_neutralizes_ablated_features(): void
    {
        $context = $this->makeContext();
        $features = new FeatureEngine();

        $full = $features->build($context);
        $ablated = $features->build($context, ['form', 'h2h', 'team_strength']);

        // Form neutralized to coin-flip.
        $this->assertSame(50.0, $ablated['home_form_score']);
        $this->assertSame(50.0, $ablated['away_form_score']);

        // H2H neutralized.
        $this->assertSame(50.0, $ablated['h2h_score']);

        // Team strength neutralized to league-average (1.0).
        $this->assertSame(1.0, $ablated['home_attack_strength']);
        $this->assertSame(1.0, $ablated['away_defense_strength']);

        // The full build must NOT be mutated.
        $this->assertNotSame(50.0, $full['home_form_score']);
    }
}
