<?php

namespace Tests\Unit;

use App\Services\Prediction\Calibration\ModelComparisonService;
use App\Services\Prediction\Validation\SignificanceService;
use Tests\TestCase;

class SignificanceServiceTest extends TestCase
{
    protected SignificanceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('evaluation.significance', [
            'alpha' => 0.05,
            'minimum_sample' => 100,
        ]);

        $this->service = new SignificanceService(
            $this->createMock(ModelComparisonService::class)
        );
    }

    public function test_insufficient_when_sample_below_minimum(): void
    {
        $r = $this->service->compareProportions(30, 80, 40, 90);

        $this->assertSame(SignificanceService::VERDICT_INSUFFICIENT, $r['verdict']);
        $this->assertStringContainsString('Insufficient evidence of improvement', $r['message']);
    }

    public function test_significant_improvement(): void
    {
        // 55/100 vs 70/100 → +15 pts, clearly significant at α=0.05.
        $r = $this->service->compareProportions(55, 100, 70, 100);

        $this->assertSame(SignificanceService::VERDICT_IMPROVEMENT, $r['verdict']);
        $this->assertEqualsWithDelta(15.0, $r['diff_points'], 0.01);
        $this->assertLessThan(0.05, $r['p_value']);
    }

    public function test_inconclusive_when_difference_not_significant(): void
    {
        // 55/100 vs 60/100 → +5 pts, not statistically significant.
        $r = $this->service->compareProportions(55, 100, 60, 100);

        $this->assertSame(SignificanceService::VERDICT_INSUFFICIENT, $r['verdict']);
        $this->assertSame('Insufficient evidence of improvement.', $r['message']);
    }

    public function test_significant_regression(): void
    {
        // 70/100 vs 55/100 → −15 pts, significant regression.
        $r = $this->service->compareProportions(70, 100, 55, 100);

        $this->assertSame(SignificanceService::VERDICT_REGRESSION, $r['verdict']);
        $this->assertEqualsWithDelta(-15.0, $r['diff_points'], 0.01);
    }

    public function test_confidence_interval_contains_the_difference(): void
    {
        $r = $this->service->compareProportions(55, 100, 70, 100);

        // The difference must sit strictly inside its own confidence interval.
        $this->assertGreaterThan($r['diff_points'], $r['ci_upper']);
        $this->assertLessThan($r['diff_points'], $r['ci_lower']);
        $this->assertGreaterThan(0.0, $r['ci_lower']); // excludes zero → significant
    }

    public function test_normal_cdf_is_sane(): void
    {
        $this->assertEqualsWithDelta(0.5, $this->service->normalCdf(0.0), 1e-9);
        $this->assertEqualsWithDelta(0.975, $this->service->normalCdf(1.96), 0.001);
    }

    public function test_inverse_normal_cdf_round_trips(): void
    {
        $this->assertEqualsWithDelta(1.96, $this->service->inverseNormalCdf(0.975), 0.001);
        $this->assertEqualsWithDelta(1.96, $this->service->zCritical(), 0.01);
    }
}
