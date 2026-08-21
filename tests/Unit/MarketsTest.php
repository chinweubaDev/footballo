<?php

namespace Tests\Unit;

use App\Services\Prediction\Markets\BttsMarket;
use App\Services\Prediction\Markets\CorrectScoreMarket;
use App\Services\Prediction\Markets\DoubleChanceMarket;
use App\Services\Prediction\Markets\DrawMarket;
use App\Services\Prediction\Markets\GoalsMarket;
use App\Services\Prediction\Markets\OneXTwoMarket;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class MarketsTest extends TestCase
{
    use PredictionTestHelpers;

    public function test_one_x_two_selects_maximum(): void
    {
        $out = (new OneXTwoMarket())->calculate($this->makeResult(2.0, 1.0));

        $this->assertSame('home', $out['selection']);
        $this->assertGreaterThanOrEqual(0, $out['probability']);
        $this->assertLessThanOrEqual(100, $out['probability']);
    }

    public function test_draw_market_returns_draw_probability(): void
    {
        $result = $this->makeResult(1.0, 1.0);
        $out = (new DrawMarket())->calculate($result);

        $this->assertSame('draw', $out['selection']);
        $this->assertEqualsWithDelta($result->drawProbability, $out['probability'], 0.01);
    }

    public function test_double_chance_combines_outcomes(): void
    {
        $result = $this->makeResult(2.0, 1.0);
        $out = (new DoubleChanceMarket())->calculate($result);

        $this->assertContains($out['selection'], ['1x', 'x2', '12']);
        $this->assertEqualsWithDelta(
            $result->homeProbability + $result->drawProbability,
            $result->doubleChance['1x'],
            0.01
        );
    }

    public function test_over_1_5_is_likely_with_strong_attack(): void
    {
        $out = (new GoalsMarket('over_1_5', 1.5))->calculate($this->makeResult(2.0, 1.0));

        $this->assertSame('over_1_5', $out['selection']);
        $this->assertGreaterThan(50, $out['probability']);
    }

    public function test_over_2_5_is_unlikely_with_low_goals(): void
    {
        $out = (new GoalsMarket('over_2_5', 2.5))->calculate($this->makeResult(0.5, 0.5));

        $this->assertSame('under_2_5', $out['selection']);
    }

    public function test_btts_yes_when_both_teams_score(): void
    {
        $out = (new BttsMarket())->calculate($this->makeResult(2.0, 2.0));

        $this->assertSame('yes', $out['selection']);
    }

    public function test_btts_no_for_zero_zero(): void
    {
        $out = (new BttsMarket())->calculate($this->makeResult(0.01, 0.01));

        $this->assertSame('no', $out['selection']);
    }

    public function test_correct_score_returns_top_candidates(): void
    {
        $out = (new CorrectScoreMarket())->calculate($this->makeResult(1.86, 0.94));

        $this->assertArrayHasKey('scores', $out);
        $this->assertGreaterThanOrEqual(2, count($out['scores']));
        $this->assertStringContainsString('-', $out['selection']);
    }
}
