<?php

namespace Tests\Unit;

use App\Services\Prediction\Calibration\ProbabilityCalibrator;
use PHPUnit\Framework\TestCase;

class ProbabilityCalibratorTest extends TestCase
{
    /**
     * Deterministic synthetic dataset with systematic overconfidence:
     * predicted [55,65,75,85,95] but actual [52,60,68,75,80].
     *
     * @return array{0:list<float>,1:list<int>}
     */
    protected function synthetic(): array
    {
        $levels = [55.0, 65.0, 75.0, 85.0, 95.0];
        $actual = [52.0, 60.0, 68.0, 75.0, 80.0];
        $n = 200;

        $probs = [];
        $outcomes = [];

        foreach ($levels as $i => $level) {
            $wins = (int) round($n * $actual[$i] / 100.0);

            for ($k = 0; $k < $n; $k++) {
                $probs[] = $level;
                $outcomes[] = $k < $wins ? 1 : 0;
            }
        }

        return [$probs, $outcomes];
    }

    public function test_platt_reduces_overconfidence(): void
    {
        [$probs, $outcomes] = $this->synthetic();

        $calibrator = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::PLATT);

        $calibrated95 = $calibrator->predict(95.0);

        // Raw 95 vs actual ~80: calibration must reduce the gap.
        $this->assertLessThan(95.0, $calibrated95);
        $this->assertLessThan(abs(95.0 - 80.0), abs($calibrated95 - 80.0));
    }

    public function test_isotonic_reduces_overconfidence(): void
    {
        [$probs, $outcomes] = $this->synthetic();

        $calibrator = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::ISOTONIC);

        $calibrated95 = $calibrator->predict(95.0);

        $this->assertLessThan(95.0, $calibrated95);
        $this->assertLessThan(abs(95.0 - 80.0), abs($calibrated95 - 80.0));
    }

    public function test_isotonic_is_monotone(): void
    {
        [$probs, $outcomes] = $this->synthetic();

        $calibrator = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::ISOTONIC);

        $prev = -1.0;

        foreach ([50.0, 60.0, 70.0, 80.0, 90.0, 95.0] as $p) {
            $calibrated = $calibrator->predict($p);
            $this->assertGreaterThanOrEqual($prev, $calibrated);
            $prev = $calibrated;
        }
    }

    public function test_platt_parameters_round_trip(): void
    {
        [$probs, $outcomes] = $this->synthetic();

        $calibrator = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::PLATT);
        $restored = ProbabilityCalibrator::fromParameters($calibrator->parameters());

        $this->assertEquals($calibrator->predict(75.0), $restored->predict(75.0));
    }

    public function test_does_not_hardcode_values(): void
    {
        // Different data must produce different calibration.
        $probs = array_merge(array_fill(0, 100, 90.0), array_fill(0, 100, 70.0));
        $outcomes = array_merge(array_fill(0, 70, 1), array_fill(0, 30, 0), array_fill(0, 70, 1), array_fill(0, 30, 0));

        $calibrator = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::PLATT);

        // A flat 70%-success dataset should keep mid-range probabilities near 70.
        $this->assertGreaterThan(60.0, $calibrator->predict(70.0));
        $this->assertLessThan(80.0, $calibrator->predict(70.0));
    }

    public function test_rejects_mismatched_inputs(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProbabilityCalibrator())->fit([50.0, 60.0], [1]);
    }
}
