<?php

namespace Tests\Unit;

use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\TestCase;

/**
 * Phase 1O §10 — adaptive full-range probability bins.
 *
 * Regresses the historical bug where low-probability markets (Draw, Correct
 * Score) fell below the 50% bucket floor and were reported as ECE = 0.
 */
class CalibrationBucketTest extends TestCase
{
    protected MetricsCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new MetricsCalculator();
    }

    public function test_low_probability_predictions_fall_into_buckets(): void
    {
        $rows = [
            $this->row('won', 20),
            $this->row('lost', 20),
            $this->row('won', 20),
            $this->row('lost', 20),
        ];

        $buckets = $this->calculator->probabilityBuckets($rows);
        $labels = array_column($buckets, 'label');

        // The full range is covered — a 20% prediction must land in 20-29.
        $this->assertContains('0-9', $labels);
        $this->assertContains('20-29', $labels);

        foreach ($buckets as $bucket) {
            if ($bucket['label'] === '20-29') {
                $this->assertSame(4, $bucket['total']);
                $this->assertSame(50.0, $bucket['accuracy']);
            }
        }
    }

    public function test_ece_is_not_zero_for_low_probability_market(): void
    {
        // Correct-score-like distribution: predicted 20%, actual success 50%.
        $rows = [
            $this->row('won', 20),
            $this->row('lost', 20),
            $this->row('won', 20),
            $this->row('lost', 20),
        ];

        $ece = $this->calculator->expectedCalibrationError($rows);

        $this->assertNotNull($ece);
        $this->assertGreaterThan(0.0, $ece);
    }

    protected function row(string $result, float $probability): array
    {
        return [
            'result' => $result,
            'probability' => $probability,
            'confidence' => 60,
            'market_code' => 'correct_score',
            'league_id' => 39,
            'model_version' => 'v1.0.0',
        ];
    }
}
