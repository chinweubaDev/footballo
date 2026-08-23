<?php

namespace Tests\Feature;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\League;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\Validation\MultiSeasonAnalysisService;
use App\Services\Prediction\Validation\ValidationMatrixService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1P §4/§5 — multi-season aggregation and pooled accuracy.
 *
 * Pooled accuracy MUST be total wins ÷ total resolved (never a blind average
 * of per-season percentages).
 */
class MultiSeasonAnalysisTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
        ]);
    }

    protected function makeRun(int $league, int $season): BacktestRun
    {
        return BacktestRun::create([
            'name' => "L{$league} S{$season}",
            'league_id' => $league,
            'season' => $season,
            'model_version' => 'v1.0.0',
            'status' => BacktestRun::STATUS_COMPLETED,
            'total_fixtures' => 100,
        ]);
    }

    protected function makePredictions(int $runId, string $market, int $wins, int $losses): void
    {
        for ($i = 0; $i < $wins; $i++) {
            BacktestPrediction::create([
                'backtest_run_id' => $runId,
                'fixture_id' => 1,
                'market_code' => $market,
                'probability' => 70,
                'confidence' => 75,
                'model_version' => 'v1.0.0',
                'result' => 'won',
            ]);
        }

        for ($i = 0; $i < $losses; $i++) {
            BacktestPrediction::create([
                'backtest_run_id' => $runId,
                'fixture_id' => 1,
                'market_code' => $market,
                'probability' => 70,
                'confidence' => 75,
                'model_version' => 'v1.0.0',
                'result' => 'lost',
            ]);
        }
    }

    protected function service(): MultiSeasonAnalysisService
    {
        return new MultiSeasonAnalysisService(new ValidationMatrixService(), new MetricsCalculator());
    }

    public function test_pooled_accuracy_is_not_a_blind_average(): void
    {
        $run2023 = $this->makeRun(39, 2023);
        $run2024 = $this->makeRun(39, 2024);

        // 2023: 90/100 won (90%); 2024: 10/200 won (5%).
        $this->makePredictions($run2023->id, '1x2', 90, 10);
        $this->makePredictions($run2024->id, '1x2', 10, 190);

        $rows = $this->service()->resolvedRows();

        // Pooled = 100 wins / 300 resolved = 33.33%, not 47.5%.
        $pooled = $this->service()->pooledAccuracy($rows);
        $this->assertSame(33.33, $pooled);
    }

    public function test_seasons_are_separated_in_market_generalization(): void
    {
        $run2023 = $this->makeRun(39, 2023);
        $run2024 = $this->makeRun(39, 2024);

        $this->makePredictions($run2023->id, '1x2', 80, 20);
        $this->makePredictions($run2024->id, '1x2', 20, 80);

        $market = collect($this->service()->marketGeneralization())->firstWhere('market_code', '1x2');

        $this->assertNotNull($market);
        $this->assertCount(2, $market['per_season']);
        $this->assertSame([2023, 2024], array_column($market['per_season'], 'season'));
        $this->assertSame(80.0, $market['per_season'][0]['accuracy']);
        $this->assertSame(20.0, $market['per_season'][1]['accuracy']);
        $this->assertSame(2, $market['pooled']['n_seasons']);
    }

    public function test_market_generalization_reports_spread_statistics(): void
    {
        $run2023 = $this->makeRun(39, 2023);
        $run2024 = $this->makeRun(39, 2024);

        $this->makePredictions($run2023->id, '1x2', 60, 40); // 60%
        $this->makePredictions($run2024->id, '1x2', 80, 20); // 80%

        $market = collect($this->service()->marketGeneralization())->firstWhere('market_code', '1x2');
        $g = $market['generalization'];

        $this->assertSame(70.0, $g['mean']);
        $this->assertSame(60.0, $g['min']);
        $this->assertSame(80.0, $g['max']);
        $this->assertSame(10.0, $g['std']);
    }
}
