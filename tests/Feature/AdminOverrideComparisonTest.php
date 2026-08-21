<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\Evaluation\MarketResultResolver;
use App\Services\Prediction\Evaluation\PredictionResultService;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\Admin\AuditLogger;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class AdminOverrideComparisonTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_original_model_won_but_admin_override_lost(): void
    {
        $fixture = Fixture::create([
            'api_fixture_id' => 6001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'status' => 'FT',
            'home_goals' => 2,
            'away_goals' => 1,
            'match_date' => now()->subDay(),
        ]);

        // AI = Arsenal (home), admin overrides to Draw.
        $prediction = Prediction::create([
            'fixture_id' => $fixture->id,
            'league_id' => 39,
            'market_code' => '1x2',
            'selection' => 'home',
            'admin_selection' => 'draw',
            'probability' => 76,
            'confidence' => 80,
            'model_version' => 'v1.0.0',
            'status' => 'published',
        ]);

        $resolver = new PredictionResultService(new MarketResultResolver(), new AuditLogger());
        $resolver->resolvePrediction($prediction, $fixture);

        $prediction->refresh();

        // Original model = WON, admin override = LOST, effective = LOST.
        $this->assertSame('won', $prediction->model_result);
        $this->assertSame('lost', $prediction->override_result);
        $this->assertSame('lost', $prediction->result);

        // Dashboard comparison reflects both outcomes.
        $performance = new PerformanceAnalyticsService(new MetricsCalculator());
        $override = $performance->overridePerformance();

        $this->assertSame(1, $override['model']['won']);
        $this->assertSame(0, $override['model']['lost']);
        $this->assertSame(100.0, $override['model']['accuracy']);

        $this->assertSame(0, $override['override']['won']);
        $this->assertSame(1, $override['override']['lost']);
        $this->assertSame(0.0, $override['override']['accuracy']);

        $this->assertSame(1, $override['overridden_count']);
    }

    public function test_no_bet_not_counted_as_won_or_lost(): void
    {
        $rows = [
            ['fixture_id' => 1, 'market_code' => '1x2', 'probability' => 76.0, 'confidence' => 80, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'won', 'status' => 'published'],
            ['fixture_id' => 1, 'market_code' => 'over_2_5', 'probability' => 45.0, 'confidence' => 40, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'lost', 'status' => 'no_bet'],
            ['fixture_id' => 2, 'market_code' => '1x2', 'probability' => 60.0, 'confidence' => 70, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'lost', 'status' => 'published'],
        ];

        $performance = new PerformanceAnalyticsService(new MetricsCalculator());

        // Overview accuracy only counts bettable (non-NO_BET) predictions.
        $overview = $performance->overview($rows);
        $this->assertSame(2, $overview['resolved']);
        $this->assertSame(1, $overview['won']);
        $this->assertSame(1, $overview['lost']);
        $this->assertSame(50.0, $overview['accuracy']);

        // NO_BET analysis counts the declined selection separately.
        $noBet = $performance->noBetAnalysis($rows);
        $this->assertSame(1, $noBet['count']);
        $this->assertSame(1, $noBet['would_be']['resolved']);
        $this->assertSame(0, $noBet['would_be']['won']);
    }
}
