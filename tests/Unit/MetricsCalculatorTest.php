<?php

namespace Tests\Unit;

use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\TestCase;

class MetricsCalculatorTest extends TestCase
{
    protected MetricsCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new MetricsCalculator();
    }

    protected function row(string $result, float $probability, int $confidence = 75, string $market = '1x2', ?string $status = null): array
    {
        return [
            'market_code' => $market,
            'probability' => $probability,
            'confidence' => $confidence,
            'model_version' => 'v1.0.0',
            'league_id' => 39,
            'data_quality_score' => 80,
            'result' => $result,
            'status' => $status,
        ];
    }

    public function test_accuracy_excludes_voids(): void
    {
        $rows = [
            $this->row('won', 80),
            $this->row('lost', 70),
            $this->row('won', 90),
            $this->row('void', 75),
        ];

        $summary = $this->calculator->summarize($rows);

        $this->assertSame(4, $summary['total']);
        $this->assertSame(2, $summary['won']);
        $this->assertSame(1, $summary['lost']);
        $this->assertSame(1, $summary['void']);
        $this->assertEquals(66.67, $summary['accuracy']);
    }

    public function test_brier_score_perfect_prediction_is_zero(): void
    {
        $rows = [
            $this->row('won', 100),
            $this->row('lost', 0),
        ];

        $this->assertEquals(0.0, $this->calculator->brierScore($rows));
    }

    public function test_brier_score_is_mean_squared_error(): void
    {
        $rows = [
            $this->row('won', 80),   // p=0.8, y=1 -> (0.2)^2 = 0.04
            $this->row('lost', 60),  // p=0.6, y=0 -> (0.6)^2 = 0.36
        ];

        $this->assertEquals(0.2, $this->calculator->brierScore($rows));
    }

    public function test_log_loss_protects_against_log_zero(): void
    {
        $rows = [
            $this->row('won', 100),
            $this->row('lost', 0),
        ];

        $loss = $this->calculator->logLoss($rows);

        $this->assertNotNull($loss);
        $this->assertLessThan(0.01, $loss); // near-perfect, clipped
    }

    public function test_confidence_buckets_are_correct(): void
    {
        $rows = [
            $this->row('won', 80, 55),
            $this->row('lost', 80, 65),
            $this->row('won', 80, 75),
            $this->row('won', 80, 85),
            $this->row('won', 80, 95),
        ];

        $buckets = $this->calculator->confidenceBuckets($rows);
        $labels = array_column($buckets, 'label');

        $this->assertContains('50-59', $labels);
        $this->assertContains('60-69', $labels);
        $this->assertContains('70-79', $labels);
        $this->assertContains('80-89', $labels);
        $this->assertContains('90-100', $labels);

        foreach ($buckets as $bucket) {
            if ($bucket['label'] === '90-100') {
                $this->assertSame(1, $bucket['total']);
                $this->assertSame(1, $bucket['won']);
            }
        }
    }

    public function test_probability_calibration_compares_predicted_to_actual(): void
    {
        $rows = [
            $this->row('won', 70),
            $this->row('lost', 70),
            $this->row('won', 70),
            $this->row('won', 70),
            $this->row('lost', 70),
            $this->row('won', 70),
        ];

        $buckets = $this->calculator->probabilityBuckets($rows);

        foreach ($buckets as $bucket) {
            if ($bucket['label'] === '70-79') {
                $this->assertSame(6, $bucket['total']);
                $this->assertEquals(66.67, $bucket['accuracy']);
            }
        }
    }

    public function test_selectivity_filters_by_confidence(): void
    {
        $rows = [
            $this->row('won', 80, 55),
            $this->row('lost', 80, 75),
            $this->row('won', 80, 85),
            $this->row('won', 80, 95),
        ];

        $selectivity = $this->calculator->selectivity($rows);

        $this->assertSame(4, $selectivity['all']['total']);
        $this->assertSame(3, $selectivity['70+']['total']);
        $this->assertSame(2, $selectivity['80+']['total']);
        $this->assertSame(1, $selectivity['90+']['total']);
    }

    public function test_accuracy_null_when_no_resolved(): void
    {
        $this->assertNull($this->calculator->accuracy(0, 0));
        $this->assertNull($this->calculator->summarize([])['accuracy']);
    }
}
