<?php

namespace Tests\Feature;

use App\Services\Prediction\Calibration\WalkForwardCalibrator;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\TestCase;

/**
 * MANDATORY data-leakage test for calibration: validation-period data must
 * never be used to fit the calibration model.
 */
class WalkForwardCalibratorTest extends TestCase
{
    protected function row(string $market, float $probability, string $result, string $t): array
    {
        return ['market_code' => $market, 'probability' => $probability, 'result' => $result, 't' => $t];
    }

    public function test_training_uses_only_chronologically_earlier_data(): void
    {
        // Training period: 140 rows at p=90 with ~70% success.
        $rows = [];

        for ($i = 0; $i < 140; $i++) {
            $result = ($i < 98) ? 'won' : 'lost'; // 70% success
            $rows[] = $this->row('1x2', 90.0, $result, sprintf('2025-01-%02d 15:00:00', ($i % 28) + 1));
        }

        // Validation period: 60 rows at p=90 with 100% success (different pattern).
        for ($i = 0; $i < 60; $i++) {
            $rows[] = $this->row('1x2', 90.0, 'won', sprintf('2025-07-%02d 15:00:00', ($i % 28) + 1));
        }

        $calibrator = new WalkForwardCalibrator(new MetricsCalculator());
        $report = $calibrator->fitAndEvaluate($rows, 0.7);

        $market = $report['per_market']['1x2'];

        $this->assertTrue($market['trained']);
        // Exactly the 140 earlier rows were used for training; the 60 later
        // (100%-success) rows were held out as validation.
        $this->assertSame(140, $market['train_count']);
        $this->assertSame(60, $market['validation_count']);

        // The trained calibrator maps 90 -> ~70 (training pattern), NOT 100%.
        $model = $report['models']['1x2'];
        $calibrated = $model->predict(90.0);

        $this->assertLessThan(85.0, $calibrated);
        $this->assertGreaterThan(60.0, $calibrated);
    }

    public function test_insufficient_training_data_skips_market(): void
    {
        $rows = [];

        for ($i = 0; $i < 10; $i++) {
            $rows[] = $this->row('draw', 30.0, 'lost', "2025-01-{$i} 15:00:00");
        }

        $calibrator = new WalkForwardCalibrator(new MetricsCalculator());
        $report = $calibrator->fitAndEvaluate($rows, 0.7);

        $this->assertFalse($report['per_market']['draw']['trained']);
        $this->assertSame('insufficient_training_data', $report['per_market']['draw']['reason']);
    }

    public function test_correct_score_is_not_binary_calibrated(): void
    {
        $rows = [];

        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('correct_score', 12.0, 'lost', "2025-01-{$i} 15:00:00");
        }

        $calibrator = new WalkForwardCalibrator(new MetricsCalculator());
        $report = $calibrator->fitAndEvaluate($rows, 0.7);

        $this->assertArrayNotHasKey('correct_score', $report['per_market']);
    }
}
