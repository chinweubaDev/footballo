<?php

namespace Tests\Feature;

use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use Tests\TestCase;

class PerformanceAnalyticsTest extends TestCase
{
    protected function row(string $result, float $probability, int $confidence, string $market, string $status = 'published', ?int $league = 39, ?int $quality = 80, int $fixture = 1): array
    {
        return [
            'fixture_id' => $fixture,
            'market_code' => $market,
            'probability' => $probability,
            'confidence' => $confidence,
            'model_version' => 'v1.0.0',
            'league_id' => $league,
            'data_quality_score' => $quality,
            'result' => $result,
            'status' => $status,
        ];
    }

    public function test_coverage_reflects_selectivity(): void
    {
        $rows = [
            $this->row('won', 80, 85, '1x2'),
        ];

        $service = new PerformanceAnalyticsService(new MetricsCalculator());
        $overview = $service->overview($rows);

        $this->assertSame(1, $overview['total_fixtures']);
        $this->assertSame(1, $overview['predicted_fixtures']);
        $this->assertSame(100.0, $overview['coverage_percent']);
    }

    public function test_coverage_excludes_no_bet_fixtures(): void
    {
        $rows = [
            $this->row('won', 80, 85, '1x2', 'published', fixture: 1),
            $this->row('lost', 40, 40, '1x2', 'no_bet', fixture: 2),
        ];

        $service = new PerformanceAnalyticsService(new MetricsCalculator());
        $overview = $service->overview($rows);

        $this->assertSame(2, $overview['total_fixtures']);
        $this->assertSame(1, $overview['predicted_fixtures']);
        $this->assertSame(50.0, $overview['coverage_percent']);
    }

    public function test_market_ranking_respects_minimum_sample_size(): void
    {
        config()->set('evaluation.minimum_sample_size', 3);

        $rows = [];

        // 1x2: 4 resolved predictions → ranked
        for ($i = 0; $i < 4; $i++) {
            $rows[] = $this->row($i < 3 ? 'won' : 'lost', 75, 80, '1x2');
        }

        // correct_score: only 1 resolved prediction → insufficient
        $rows[] = $this->row('lost', 10, 50, 'correct_score');

        $service = new PerformanceAnalyticsService(new MetricsCalculator());
        $ranking = $service->marketRanking($rows);

        $this->assertSame(3, $ranking['minimum_sample_size']);

        $rankedCodes = array_column($ranking['ranked'], 'market_code');
        $insufficientCodes = array_column($ranking['insufficient'], 'market_code');

        $this->assertContains('1x2', $rankedCodes);
        $this->assertContains('correct_score', $insufficientCodes);
    }

    public function test_market_ranking_is_data_driven_not_hardcoded(): void
    {
        config()->set('evaluation.minimum_sample_size', 2);

        $rows = [
            $this->row('won', 80, 85, 'over_1_5'),
            $this->row('won', 80, 85, 'over_1_5'),
            $this->row('won', 80, 85, '1x2'),
            $this->row('lost', 80, 85, '1x2'),
        ];

        $service = new PerformanceAnalyticsService(new MetricsCalculator());
        $ranking = $service->marketRanking($rows);

        $ranked = $ranking['ranked'];

        // over_1_5 has 100% accuracy and must rank first.
        $this->assertSame('over_1_5', $ranked[0]['market_code']);
        $this->assertSame(100.0, $ranked[0]['accuracy']);
    }
}
