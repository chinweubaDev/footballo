<?php

namespace Tests\Feature;

use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use Tests\TestCase;

/**
 * Phase 1M §14 — published vs no-bet analysis.
 *
 * The publication gate must be judged on whether it actually selects stronger
 * predictions. NO_BET predictions are never counted as real bets, but their
 * would-be outcome is reported for gate evaluation.
 */
class PublishedNoBetAnalysisTest extends TestCase
{
    public function test_no_bet_analysis_reports_would_be_outcome(): void
    {
        $rows = [
            ['fixture_id' => 1, 'market_code' => '1x2', 'probability' => 75.0, 'confidence' => 80, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'won', 'status' => 'published'],
            ['fixture_id' => 2, 'market_code' => '1x2', 'probability' => 76.0, 'confidence' => 78, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'lost', 'status' => 'published'],
            ['fixture_id' => 3, 'market_code' => 'over_2_5', 'probability' => 55.0, 'confidence' => 45, 'model_version' => 'v1.0.0', 'league_id' => 39, 'data_quality_score' => 80, 'result' => 'won', 'status' => 'no_bet'],
        ];

        $analysis = (new PerformanceAnalyticsService(new MetricsCalculator()))->noBetAnalysis($rows);

        // One declined prediction, whose would-be outcome was a win.
        $this->assertSame(1, $analysis['count']);
        $this->assertSame(1, $analysis['would_be']['won']);
        $this->assertSame(0, $analysis['would_be']['lost']);
        $this->assertSame(100.0, $analysis['would_be']['accuracy']);

        // Published (bettable) accuracy is separate and does NOT include no_bet.
        $this->assertSame(3, $analysis['total_fixtures']);
        $this->assertSame(2, $analysis['predicted_fixtures']);
    }
}
