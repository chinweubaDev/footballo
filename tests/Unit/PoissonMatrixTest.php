<?php

namespace Tests\Unit;

use App\Services\Prediction\Support\PoissonMatrix;
use PHPUnit\Framework\TestCase;

class PoissonMatrixTest extends TestCase
{
    public function test_pmf_zero_goals_matches_exp_negative_lambda(): void
    {
        $this->assertEqualsWithDelta(exp(-2.0), PoissonMatrix::pmf(0, 2.0), 0.0001);
    }

    public function test_score_matrix_normalizes_to_100(): void
    {
        $matrix = PoissonMatrix::build(2.0, 1.0, 6);

        $this->assertEqualsWithDelta(100.0, array_sum($matrix), 0.5);
    }

    public function test_one_x_two_sums_to_100(): void
    {
        $matrix = PoissonMatrix::build(1.86, 0.94, 6);
        $oneXTwo = PoissonMatrix::oneXTwo($matrix);

        $this->assertEqualsWithDelta(100.0, array_sum($oneXTwo), 0.5);
    }

    public function test_over_is_monotonic(): void
    {
        $matrix = PoissonMatrix::build(2.0, 1.0, 6);

        $this->assertGreaterThanOrEqual(PoissonMatrix::over($matrix, 2.5), PoissonMatrix::over($matrix, 1.5));
    }

    public function test_btts_is_zero_for_zero_goals(): void
    {
        $matrix = PoissonMatrix::build(0.01, 0.01, 6);

        $this->assertLessThan(1.0, PoissonMatrix::btts($matrix));
    }

    public function test_top_scores_are_sorted_descending(): void
    {
        $matrix = PoissonMatrix::build(1.86, 0.94, 6);
        $top = PoissonMatrix::topScores($matrix, 5);

        $this->assertCount(5, $top);
        $this->assertGreaterThanOrEqual($top[1]['probability'], $top[0]['probability']);
        $this->assertStringContainsString('-', $top[0]['score']);
    }
}
