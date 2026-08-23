<?php

namespace Tests\Unit;

use App\Services\Prediction\Evaluation\MarketResultResolver;
use PHPUnit\Framework\TestCase;

/**
 * Phase 1N §21 — verify every market against the full required scoreline set.
 *
 * The expected outcomes are derived independently in this test from the market
 * definition; the resolver must agree on every one of the 15 scorelines.
 */
class MarketSettlementMatrixTest extends TestCase
{
    protected MarketResultResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new MarketResultResolver();
    }

    /**
     * 0-0, 1-0, 0-1, 1-1, 2-0, 0-2, 2-1, 1-2, 2-2, 3-0, 0-3, 3-1, 1-3, 3-2, 2-3.
     *
     * @return list<array{0:int,1:int}>
     */
    public static function scorelines(): array
    {
        return [
            [0, 0], [1, 0], [0, 1], [1, 1], [2, 0], [0, 2],
            [2, 1], [1, 2], [2, 2], [3, 0], [0, 3], [3, 1],
            [1, 3], [3, 2], [2, 3],
        ];
    }

    public function test_all_seven_markets_resolve_on_all_required_scorelines(): void
    {
        foreach (self::scorelines() as [$home, $away]) {
            $total = $home + $away;
            $score = "{$home}-{$away}";

            // 1X2
            $this->assertResolution('1x2', 'home', $home, $away, $home > $away);
            $this->assertResolution('1x2', 'draw', $home, $away, $home === $away);
            $this->assertResolution('1x2', 'away', $home, $away, $home < $away);

            // Draw market
            $this->assertResolution('draw', 'draw', $home, $away, $home === $away);

            // Double chance
            $this->assertResolution('double_chance', '1x', $home, $away, $home >= $away);
            $this->assertResolution('double_chance', 'x2', $home, $away, $home <= $away);
            $this->assertResolution('double_chance', '12', $home, $away, $home !== $away);

            // Goals lines (over/under)
            $this->assertResolution('over_1_5', 'over_1_5', $home, $away, $total > 1.5);
            $this->assertResolution('over_1_5', 'under_1_5', $home, $away, $total <= 1.5);
            $this->assertResolution('over_2_5', 'over_2_5', $home, $away, $total > 2.5);
            $this->assertResolution('over_2_5', 'under_2_5', $home, $away, $total <= 2.5);

            // BTTS
            $bothScored = $home >= 1 && $away >= 1;
            $this->assertResolution('btts', 'yes', $home, $away, $bothScored);
            $this->assertResolution('btts', 'no', $home, $away, ! $bothScored);

            // Correct score
            $this->assertResolution('correct_score', $score, $home, $away, true);
            $this->assertResolution('correct_score', '9-9', $home, $away, false);
        }
    }

    protected function assertResolution(string $market, string $selection, int $home, int $away, bool $expectedWon): void
    {
        $actual = $this->resolver->resolve($market, $selection, $home, $away);

        $this->assertSame(
            $expectedWon ? 'won' : 'lost',
            $actual,
            "market={$market} selection={$selection} score={$home}-{$away}"
        );
    }
}
