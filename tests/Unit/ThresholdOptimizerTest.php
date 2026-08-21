<?php

namespace Tests\Unit;

use App\Services\Prediction\Calibration\ThresholdOptimizer;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\TestCase;

class ThresholdOptimizerTest extends TestCase
{
    protected function row(string $market, float $probability, string $result): array
    {
        return ['market_code' => $market, 'probability' => $probability, 'result' => $result];
    }

    public function test_sweep_reports_accuracy_and_coverage(): void
    {
        $rows = [];

        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('1x2', 80.0, 'won');
        }

        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('1x2', 55.0, 'lost');
        }

        $optimizer = new ThresholdOptimizer(new MetricsCalculator());
        $sweep = $optimizer->sweep($rows, 100)['1x2'];

        $at80 = collect($sweep)->firstWhere('threshold', 80);
        $this->assertSame(100, $at80['predictions']);
        $this->assertSame(100.0, $at80['accuracy']);
        $this->assertSame(50.0, $at80['coverage_percent']);

        // Coverage must shrink as the threshold rises.
        $at90 = collect($sweep)->firstWhere('threshold', 90);
        $this->assertSame(0, $at90['predictions']);
        $this->assertSame(0.0, $at90['coverage_percent']);
    }

    public function test_minimum_sample_size_guards_thresholds(): void
    {
        $rows = [
            $this->row('1x2', 95.0, 'won'),
            $this->row('1x2', 90.0, 'lost'),
        ];

        $optimizer = new ThresholdOptimizer(new MetricsCalculator());
        $sweep = $optimizer->sweep($rows, 100)['1x2'];

        $at90 = collect($sweep)->firstWhere('threshold', 90);
        $this->assertSame(2, $at90['predictions']);
        $this->assertTrue($at90['insufficient_sample']);
    }

    public function test_sweep_is_per_market(): void
    {
        $rows = [
            $this->row('1x2', 80.0, 'won'),
            $this->row('1x2', 80.0, 'won'),
            $this->row('over_1_5', 80.0, 'lost'),
            $this->row('over_1_5', 80.0, 'lost'),
        ];

        $optimizer = new ThresholdOptimizer(new MetricsCalculator());
        $sweep = $optimizer->sweep($rows, 1);

        $this->assertArrayHasKey('1x2', $sweep);
        $this->assertArrayHasKey('over_1_5', $sweep);

        $at80 = collect($sweep['1x2'])->firstWhere('threshold', 80);
        $this->assertSame(100.0, $at80['accuracy']);
    }
}
