<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Services\Prediction\HomepagePredictionService;
use App\Services\Prediction\PublicPredictionService;
use Database\Seeders\PredictionCategorySeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class HomepagePredictionTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected League $league;
    protected Fixture $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        $this->league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'homepage_enabled' => true,
        ]);

        $this->fixture = Fixture::create([
            'api_fixture_id' => 1001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
            'slug' => 'arsenal-vs-chelsea',
        ]);
    }

    protected function makePrediction(string $market, string $selection, float $probability, int $confidence, string $status = 'published', ?string $adminSelection = null): Prediction
    {
        return Prediction::create([
            'fixture_id' => $this->fixture->id,
            'league_id' => 39,
            'market_code' => $market,
            'category' => strtoupper($market),
            'selection' => $selection,
            'probability' => $probability,
            'confidence' => $confidence,
            'model_version' => 'v1.0.0',
            'status' => $status,
            'admin_selection' => $adminSelection,
        ]);
    }

    protected function service(): HomepagePredictionService
    {
        return new HomepagePredictionService(new PublicPredictionService());
    }

    public function test_sure_picks_are_strictly_1x2(): void
    {
        $this->makePrediction('1x2', 'home', 76, 86);
        $this->makePrediction('double_chance', '1x', 91, 93);
        $this->makePrediction('over_1_5', 'over_1_5', 85, 88);
        $this->makePrediction('over_2_5', 'over_2_5', 62, 66);
        $this->makePrediction('btts', 'yes', 68, 72);
        $this->makePrediction('draw', 'draw', 30, 55);
        $this->makePrediction('correct_score', '2-1', 13, 50);

        $surePicks = $this->service()->surePicks(10);

        $this->assertNotEmpty($surePicks);
        foreach ($surePicks as $prediction) {
            $this->assertSame('1x2', $prediction->market_code);
        }
    }

    public function test_featured_are_only_1x2_and_double_chance(): void
    {
        $this->makePrediction('1x2', 'home', 76, 86);
        $this->makePrediction('double_chance', '1x', 91, 93);
        $this->makePrediction('over_1_5', 'over_1_5', 85, 88);
        $this->makePrediction('btts', 'yes', 68, 72);
        $this->makePrediction('correct_score', '2-1', 13, 50);

        $featured = $this->service()->featuredSelections(10);

        $this->assertNotEmpty($featured);
        foreach ($featured as $prediction) {
            $this->assertContains($prediction->market_code, ['1x2', 'double_chance']);
        }
    }

    public function test_admin_override_is_the_displayed_selection(): void
    {
        $this->makePrediction('1x2', 'home', 76, 86, 'published', 'draw');

        $surePick = $this->service()->surePicks(10)->first();

        $this->assertNotNull($surePick);
        $this->assertSame('draw', $surePick->effective_selection);
    }

    public function test_no_bet_predictions_are_excluded_from_recommendations(): void
    {
        $this->makePrediction('1x2', 'home', 55, 55, 'no_bet');

        $this->assertTrue($this->service()->surePicks(10)->isEmpty());
        $this->assertTrue($this->service()->featuredSelections(10)->isEmpty());
    }
}
