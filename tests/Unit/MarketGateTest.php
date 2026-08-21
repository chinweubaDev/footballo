<?php

namespace Tests\Unit;

use App\Services\Prediction\Admin\MarketGate;
use Tests\TestCase;

class MarketGateTest extends TestCase
{
    protected MarketGate $gate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gate = new MarketGate();
    }

    public function test_no_bet_when_probability_below_threshold(): void
    {
        // §23: probability 69 < 70 → NO BET even though confidence passes.
        $this->assertSame(
            MarketGate::NO_BET,
            $this->gate->decide(69, 80, 80, 70, 75, 65),
        );
    }

    public function test_no_bet_when_confidence_below_threshold(): void
    {
        // §24: confidence 70 < 75 → NO BET even though probability passes.
        $this->assertSame(
            MarketGate::NO_BET,
            $this->gate->decide(75, 70, 80, 70, 75, 65),
        );
    }

    public function test_no_bet_when_data_quality_below_threshold(): void
    {
        $this->assertSame(
            MarketGate::NO_BET,
            $this->gate->decide(80, 80, 60, 70, 75, 65),
        );
    }

    public function test_bet_when_all_conditions_pass(): void
    {
        $this->assertSame(
            MarketGate::BET,
            $this->gate->decide(75, 80, 70, 70, 75, 65),
        );
    }

    public function test_bet_at_exact_threshold_boundary(): void
    {
        // Boundary values are inclusive: prob == min and conf == min pass.
        $this->assertSame(
            MarketGate::BET,
            $this->gate->decide(70, 75, 65, 70, 75, 65),
        );
    }
}
