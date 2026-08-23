<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Services\Prediction\AccumulatorBuilderService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1Q — premium accumulator builder.
 *
 * VIP  -> 3-leg + 5-leg tickets.
 * VVIP -> 2-leg + 5-leg + 10-leg tickets.
 * Combined probability is the honest product of per-leg probabilities.
 */
class AccumulatorBuilderServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
        ]);
    }

    protected function makeFixture(int $id): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 9000 + $id,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => "Home {$id}",
            'away_team' => "Away {$id}",
            'home_team_id' => 100 + $id,
            'away_team_id' => 200 + $id,
            'status' => 'NS',
            'match_date' => now()->addHour(),
        ]);
    }

    protected function makePrediction(int $fixtureId, string $market, float $probability, int $confidence, float $odds = 1.3): void
    {
        Prediction::create([
            'fixture_id' => $fixtureId,
            'league_id' => 39,
            'market_code' => $market,
            'selection' => $market === 'over_1_5' ? 'over_1_5' : '12',
            'probability' => $probability,
            'calibrated_probability' => $probability,
            'confidence' => $confidence,
            'odds' => $odds,
            'model_version' => 'v1.0.0',
            'status' => 'published',
        ]);
    }

    public function test_vip_builds_three_and_five_odds_targets(): void
    {
        // 8 fixtures at 1.5 odds each -> reach 3.0 in 3 legs, 5.0 in 4 legs.
        foreach (range(1, 8) as $i) {
            $f = $this->makeFixture($i);
            $this->makePrediction($f->id, 'over_1_5', 80, 80, 1.5);
        }

        $tickets = app(AccumulatorBuilderService::class)->build('vip');

        $this->assertCount(2, $tickets);
        $this->assertSame([3.0, 5.0], array_column($tickets, 'target_odds'));
        $this->assertTrue($tickets[0]['reached_target']);
        $this->assertTrue($tickets[1]['reached_target']);
        $this->assertGreaterThanOrEqual(3.0, $tickets[0]['total_odds']);
        $this->assertGreaterThanOrEqual(5.0, $tickets[1]['total_odds']);
    }

    public function test_vvip_builds_two_five_and_ten_odds_targets(): void
    {
        foreach (range(1, 10) as $i) {
            $f = $this->makeFixture($i);
            $this->makePrediction($f->id, 'over_1_5', 80, 80, 1.5);
        }

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');

        $this->assertCount(3, $tickets);
        $this->assertSame([2.0, 5.0, 10.0], array_column($tickets, 'target_odds'));
    }

    public function test_ticket_never_exceeds_five_matches(): void
    {
        // Low odds (1.1) so even the 10.0 target can't be reached in 5 legs.
        foreach (range(1, 10) as $i) {
            $f = $this->makeFixture($i);
            $this->makePrediction($f->id, 'over_1_5', 90, 90, 1.1);
        }

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');

        foreach ($tickets as $ticket) {
            $this->assertLessThanOrEqual(AccumulatorBuilderService::MAX_LEGS, count($ticket['legs']));
        }
    }

    public function test_combined_probability_is_product_of_legs(): void
    {
        // 2 legs at 1.5 odds reach the 2.0 target in 2 legs; 80% each -> 64%.
        foreach ([1, 2] as $i) {
            $f = $this->makeFixture($i);
            $this->makePrediction($f->id, 'over_1_5', 80, 80, 1.5);
        }

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');

        $this->assertCount(2, $tickets[0]['legs']);
        $this->assertSame(64.0, $tickets[0]['combined_probability']);
    }

    public function test_ticket_does_not_repeat_a_match(): void
    {
        $f = $this->makeFixture(1);
        $this->makePrediction($f->id, 'over_1_5', 80, 85, 1.5);
        $this->makePrediction($f->id, 'double_chance', 78, 80, 1.4);

        $f2 = $this->makeFixture(2);
        $this->makePrediction($f2->id, 'double_chance', 75, 75, 1.4);

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');
        $fixtureIds = collect($tickets[0]['legs'])->map(fn ($leg) => $leg['fixture']->id)->all();

        $this->assertCount(2, $fixtureIds);
        $this->assertCount(2, array_unique($fixtureIds));
    }

    public function test_excludes_very_low_accuracy_markets(): void
    {
        $f = $this->makeFixture(1);
        $this->makePrediction($f->id, 'correct_score', 10, 10, 8.0); // excluded
        $this->makePrediction($f->id, 'draw', 25, 25, 3.2);           // excluded
        $this->makePrediction($f->id, 'over_1_5', 80, 80, 1.3);       // included

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');
        $markets = collect($tickets[0]['legs'])->map(fn ($leg) => $leg['prediction']->market_code)->all();

        $this->assertNotContains('correct_score', $markets);
        $this->assertNotContains('draw', $markets);
        $this->assertContains('over_1_5', $markets);
    }

    public function test_legs_have_minimum_odds(): void
    {
        $f1 = $this->makeFixture(1);
        $this->makePrediction($f1->id, 'over_1_5', 90, 90, 1.1); // below min 1.20

        $f2 = $this->makeFixture(2);
        $this->makePrediction($f2->id, 'over_1_5', 85, 85, 1.3); // above min

        $tickets = app(AccumulatorBuilderService::class)->build('vvip');
        $odds = collect($tickets[0]['legs'])->map(fn ($leg) => $leg['odds'])->all();

        $this->assertNotContains(1.1, $odds);

        foreach ($odds as $o) {
            $this->assertGreaterThanOrEqual(AccumulatorBuilderService::MIN_ODDS, $o);
        }
    }
}
