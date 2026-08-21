<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Services\Prediction\PublicPredictionService;
use Database\Seeders\PredictionCategorySeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PublicPredictionServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected League $league;

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
    }

    protected function fixture(): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 2001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
            'slug' => 'arsenal-vs-chelsea',
        ]);
    }

    public function test_league_predictions_exclude_no_bet(): void
    {
        $fixture = $this->fixture();

        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 76, 'confidence' => 86, 'model_version' => 'v1.0.0', 'status' => 'published']);
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => 'over_2_5', 'selection' => 'over_2_5', 'probability' => 55, 'confidence' => 55, 'model_version' => 'v1.0.0', 'status' => 'no_bet']);

        $result = (new PublicPredictionService())->getLeaguePredictions($this->league, null, '7days', 20);

        $this->assertSame(1, $result->total());
        $this->assertSame(1, $result->first()->predictions->count());
        $this->assertSame('1x2', $result->first()->predictions->first()->market_code);
    }

    public function test_market_filter_returns_only_that_market(): void
    {
        $fixture = $this->fixture();

        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 76, 'confidence' => 86, 'model_version' => 'v1.0.0', 'status' => 'published']);
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => 'over_1_5', 'selection' => 'over_1_5', 'probability' => 85, 'confidence' => 88, 'model_version' => 'v1.0.0', 'status' => 'published']);

        $result = (new PublicPredictionService())->getMarketPredictions('over_1_5', 20);

        $this->assertSame(1, $result->total());
        $this->assertSame('over_1_5', $result->first()->predictions->first()->market_code);
    }

    public function test_disabled_market_is_not_returned(): void
    {
        PredictionCategory::where('code', 'over_2_5')->update(['enabled' => false]);

        $fixture = $this->fixture();
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => 'over_2_5', 'selection' => 'over_2_5', 'probability' => 70, 'confidence' => 75, 'model_version' => 'v1.0.0', 'status' => 'published']);

        $this->assertSame(0, (new PublicPredictionService())->getMarketPredictions('over_2_5', 20)->total());
    }

    public function test_disabled_league_is_not_returned(): void
    {
        $fixture = $this->fixture();
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 76, 'confidence' => 86, 'model_version' => 'v1.0.0', 'status' => 'published']);

        $this->league->update(['enabled' => false]);

        $this->assertSame(0, (new PublicPredictionService())->getLeaguePredictions($this->league, null, '7days', 20)->total());
    }

    public function test_fixture_detail_loads_only_published_predictions(): void
    {
        $fixture = $this->fixture();
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 76, 'confidence' => 86, 'model_version' => 'v1.0.0', 'status' => 'published']);
        Prediction::create(['fixture_id' => $fixture->id, 'league_id' => 39, 'market_code' => 'btts', 'selection' => 'yes', 'probability' => 55, 'confidence' => 55, 'model_version' => 'v1.0.0', 'status' => 'no_bet']);

        $detail = (new PublicPredictionService())->getFixturePredictions($fixture);

        $this->assertSame(1, $detail->predictions->count());
        $this->assertSame('1x2', $detail->predictions->first()->market_code);
    }

    public function test_league_page_renders_seo_title(): void
    {
        $this->get(route('predictions.league', 'premier-league'))
            ->assertOk()
            ->assertSee('Premier League Predictions');
    }
}
