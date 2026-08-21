<?php

namespace Tests\Unit;

use App\Services\Prediction\Evaluation\MarketResultResolver;
use PHPUnit\Framework\TestCase;

class MarketResultResolverTest extends TestCase
{
    protected MarketResultResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new MarketResultResolver();
    }

    public function test_1x2_home_wins(): void
    {
        $this->assertSame('won', $this->resolver->resolve('1x2', 'home', 2, 1));
        $this->assertSame('lost', $this->resolver->resolve('1x2', 'home', 1, 1));
        $this->assertSame('lost', $this->resolver->resolve('1x2', 'home', 0, 2));
    }

    public function test_1x2_draw(): void
    {
        $this->assertSame('won', $this->resolver->resolve('1x2', 'draw', 1, 1));
        $this->assertSame('lost', $this->resolver->resolve('1x2', 'draw', 2, 1));
    }

    public function test_1x2_away_wins(): void
    {
        $this->assertSame('won', $this->resolver->resolve('1x2', 'away', 0, 2));
        $this->assertSame('lost', $this->resolver->resolve('1x2', 'away', 2, 2));
    }

    public function test_draw_market(): void
    {
        $this->assertSame('won', $this->resolver->resolve('draw', 'draw', 0, 0));
        $this->assertSame('lost', $this->resolver->resolve('draw', 'draw', 1, 0));
    }

    public function test_over_1_5(): void
    {
        $this->assertSame('won', $this->resolver->resolve('over_1_5', 'over_1_5', 1, 1));   // 2 goals
        $this->assertSame('lost', $this->resolver->resolve('over_1_5', 'over_1_5', 1, 0));  // 1 goal
        $this->assertSame('won', $this->resolver->resolve('over_1_5', 'under_1_5', 1, 0));
        $this->assertSame('lost', $this->resolver->resolve('over_1_5', 'under_1_5', 2, 1));
    }

    public function test_over_2_5(): void
    {
        $this->assertSame('won', $this->resolver->resolve('over_2_5', 'over_2_5', 2, 1));   // 3 goals
        $this->assertSame('lost', $this->resolver->resolve('over_2_5', 'over_2_5', 1, 1));  // 2 goals
        $this->assertSame('won', $this->resolver->resolve('over_2_5', 'under_2_5', 1, 1));
        $this->assertSame('lost', $this->resolver->resolve('over_2_5', 'under_2_5', 2, 2)); // 4 goals
    }

    public function test_btts(): void
    {
        $this->assertSame('won', $this->resolver->resolve('btts', 'yes', 1, 1));
        $this->assertSame('lost', $this->resolver->resolve('btts', 'yes', 2, 0));
        $this->assertSame('won', $this->resolver->resolve('btts', 'no', 2, 0));
        $this->assertSame('lost', $this->resolver->resolve('btts', 'no', 1, 1));
    }

    public function test_double_chance(): void
    {
        $this->assertSame('won', $this->resolver->resolve('double_chance', '1x', 1, 1));   // draw
        $this->assertSame('won', $this->resolver->resolve('double_chance', '1x', 2, 1));   // home
        $this->assertSame('lost', $this->resolver->resolve('double_chance', '1x', 0, 1));  // away

        $this->assertSame('won', $this->resolver->resolve('double_chance', 'x2', 0, 1));   // away
        $this->assertSame('lost', $this->resolver->resolve('double_chance', 'x2', 1, 0));  // home

        $this->assertSame('won', $this->resolver->resolve('double_chance', '12', 1, 0));   // home
        $this->assertSame('won', $this->resolver->resolve('double_chance', '12', 0, 1));   // away
        $this->assertSame('lost', $this->resolver->resolve('double_chance', '12', 1, 1));  // draw
    }

    public function test_correct_score_exact_match_only(): void
    {
        $this->assertSame('won', $this->resolver->resolve('correct_score', '2-1', 2, 1));
        $this->assertSame('lost', $this->resolver->resolve('correct_score', '2-1', 1, 1));
        $this->assertSame('lost', $this->resolver->resolve('correct_score', '2-1', 3, 1)); // no partial credit
    }

    public function test_unknown_market_or_selection_resolves_void(): void
    {
        $this->assertSame('void', $this->resolver->resolve('over_3_5', 'over_3_5', 3, 1));
        $this->assertSame('void', $this->resolver->resolve('1x2', 'nonsense', 1, 1));
        $this->assertSame('void', $this->resolver->resolve('correct_score', 'nope', 1, 1));
    }
}
