<?php

namespace Tests\Unit;

use App\Services\Prediction\Calibration\CalibrationReportService;
use App\Services\Prediction\Calibration\GateOptimizer;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\TestCase;

class GateOptimizerTest extends TestCase
{
    protected function optimizer(): GateOptimizer
    {
        return new GateOptimizer(new MetricsCalculator(), new CalibrationReportService());
    }

    protected function configureGateOptimizer(): void
    {
        config()->set('evaluation.gate_optimizer', [
            'probability_thresholds' => [50, 55, 60, 65, 70, 75, 80, 85, 90],
            'confidence_thresholds' => [50, 55, 60, 65, 70, 75, 80, 85, 90],
            'insufficient_sample_threshold' => 50,
            'minimum_sample_size' => 100,
            'minimum_coverage' => 10.0,
            'minimum_accuracy' => 60.0,
            'max_brier' => 0.30,
            'weights' => ['accuracy' => 0.45, 'brier' => 0.30, 'coverage' => 0.25],
        ]);
        config()->set('evaluation.status_classification.strong_accuracy', 62.0);
    }

    protected function row(string $market, float $probability, int $confidence, string $result): array
    {
        return [
            'market_code' => $market,
            'probability' => $probability,
            'confidence' => $confidence,
            'result' => $result,
        ];
    }

    public function test_critical_double_chance_reproduction(): void
    {
        // §22: 380 fixtures, gate prob>=70 & conf>=75 → 14 predictions, 12 wins,
        // 85.71% accuracy, 3.68% coverage.
        $this->configureGateOptimizer();

        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $rows[] = $this->row('double_chance', 80.0, 80, 'won');
        }
        for ($i = 0; $i < 2; $i++) {
            $rows[] = $this->row('double_chance', 80.0, 80, 'lost');
        }
        for ($i = 0; $i < 366; $i++) {
            $rows[] = $this->row('double_chance', 60.0, 80, 'lost');
        }

        $grid = $this->optimizer()->grid($rows)['double_chance'];
        $point = collect($grid)->firstWhere(fn ($p) => $p['min_probability'] === 70 && $p['min_confidence'] === 75);

        $this->assertNotNull($point);
        $this->assertSame(14, $point['predictions']);
        $this->assertSame(12, $point['wins']);
        $this->assertSame(2, $point['losses']);
        $this->assertEquals(85.71, $point['accuracy']);
        $this->assertEquals(3.68, $point['coverage_percent']);
        $this->assertSame(GateOptimizer::INSUFFICIENT_SAMPLE, $point['sample_label']);
    }

    public function test_sample_labels_are_configurable(): void
    {
        $this->configureGateOptimizer();

        // 30 resolved → INSUFFICIENT SAMPLE (< 50)
        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $rows[] = $this->row('1x2', 80.0, 80, 'won');
        }
        $point = collect($this->optimizer()->grid($rows)['1x2'])
            ->firstWhere(fn ($p) => $p['min_probability'] === 70 && $p['min_confidence'] === 75);
        $this->assertSame(GateOptimizer::INSUFFICIENT_SAMPLE, $point['sample_label']);

        // 60 resolved → LOW SAMPLE (>= 50, < 100)
        $rows = [];
        for ($i = 0; $i < 60; $i++) {
            $rows[] = $this->row('1x2', 80.0, 80, 'won');
        }
        $point = collect($this->optimizer()->grid($rows)['1x2'])
            ->firstWhere(fn ($p) => $p['min_probability'] === 70 && $p['min_confidence'] === 75);
        $this->assertSame(GateOptimizer::LOW_SAMPLE, $point['sample_label']);

        // 120 resolved → SUFFICIENT SAMPLE (>= 100)
        $rows = [];
        for ($i = 0; $i < 120; $i++) {
            $rows[] = $this->row('1x2', 80.0, 80, 'won');
        }
        $point = collect($this->optimizer()->grid($rows)['1x2'])
            ->firstWhere(fn ($p) => $p['min_probability'] === 70 && $p['min_confidence'] === 75);
        $this->assertSame(GateOptimizer::SUFFICIENT_SAMPLE, $point['sample_label']);
    }

    public function test_grid_computes_brier_and_confidence_interval(): void
    {
        $this->configureGateOptimizer();

        $rows = [];
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('over_1_5', 85.0, 80, 'won');
        }
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('over_1_5', 55.0, 80, 'lost');
        }

        $grid = $this->optimizer()->grid($rows)['over_1_5'];
        $point = collect($grid)->firstWhere(fn ($p) => $p['min_probability'] === 60 && $p['min_confidence'] === 50);

        $this->assertSame(100, $point['predictions']);
        $this->assertEquals(100.0, $point['accuracy']);
        $this->assertNotNull($point['brier_score']);
        $this->assertNotNull($point['ci_lower']);
        $this->assertNotNull($point['ci_upper']);
        $this->assertNotNull($point['avg_probability']);
        $this->assertNotNull($point['avg_confidence']);
    }

    public function test_recommendation_uses_documented_composite_rule(): void
    {
        $this->configureGateOptimizer();

        // 100 high-probability winners + 100 low-probability losers.
        $rows = [];
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('1x2', 85.0, 80, 'won');
        }
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('1x2', 55.0, 80, 'lost');
        }

        $rec = $this->optimizer()->recommend($rows)['1x2'];

        // First grid point satisfying n>=100, cov>=10, acc>=60, brier<=0.30.
        $this->assertSame(60, $rec['recommended_min_probability']);
        $this->assertSame(50, $rec['recommended_min_confidence']);
        $this->assertEquals(100.0, $rec['accuracy']);
        $this->assertEquals(50.0, $rec['coverage_percent']);
        $this->assertStringContainsString('composite rule', $rec['reason']);
    }

    public function test_market_status_is_derived_not_hardcoded(): void
    {
        $this->configureGateOptimizer();

        // Sufficient data + strong candidate → CURRENT.
        $rows = [];
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('over_1_5', 85.0, 80, 'won');
        }
        for ($i = 0; $i < 100; $i++) {
            $rows[] = $this->row('over_1_5', 55.0, 80, 'lost');
        }
        $this->assertSame(GateOptimizer::STATUS_CURRENT, $this->optimizer()->marketStatus($rows));

        // Too little data → INSUFFICIENT_DATA.
        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $rows[] = $this->row('over_1_5', 80.0, 80, 'won');
        }
        $this->assertSame(GateOptimizer::STATUS_INSUFFICIENT_DATA, $this->optimizer()->marketStatus($rows));

        // Enough data but no viable gate → WEAK.
        $rows = [];
        for ($i = 0; $i < 150; $i++) {
            $rows[] = $this->row('draw', 55.0, 80, 'lost');
        }
        $this->assertSame(GateOptimizer::STATUS_WEAK, $this->optimizer()->marketStatus($rows));
    }
}
