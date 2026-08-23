<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Services\Prediction\SurePickService;
use Database\Seeders\PredictionCategorySeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class SurePickServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected League $league;
    protected Fixture $fixture;
    protected PredictionModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        $this->model = PredictionModel::create([
            'name' => 'Ensemble',
            'version' => 'v1.0.0',
            'active' => true,
        ]);

        $this->league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
        ]);

        $this->fixture = Fixture::create([
            'api_fixture_id' => 2001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now()->addDay(),
            'slug' => 'arsenal-vs-chelsea',
        ]);
    }

    protected function makePrediction(string $market, string $selection, float $calibrated, int $confidence, string $status = 'published'): Prediction
    {
        return Prediction::create([
            'fixture_id' => $this->fixture->id,
            'league_id' => 39,
            'market_code' => $market,
            'selection' => $selection,
            'probability' => $calibrated,
            'calibrated_probability' => $calibrated,
            'confidence' => $confidence,
            'model_id' => $this->model->id,
            'model_version' => 'v1.0.0',
            'status' => $status,
        ]);
    }

    public function test_sure_picks_are_strictly_1x2(): void
    {
        $this->makePrediction('1x2', 'home', 76, 86);
        $this->makePrediction('double_chance', '1x', 91, 93);
        $this->makePrediction('over_1_5', 'over_1_5', 85, 88);

        $picks = (new SurePickService())->compute(10);

        $this->assertNotEmpty($picks);
        foreach ($picks as $p) {
            $this->assertSame('1x2', $p->market_code);
        }
    }

    public function test_empty_when_nothing_qualifies(): void
    {
        $this->makePrediction('1x2', 'home', 76, 86, 'no_bet');

        $this->assertTrue((new SurePickService())->compute(10)->isEmpty());
    }

    public function test_ranks_by_calibrated_probability_and_confidence(): void
    {
        $low = $this->makePrediction('1x2', 'home', 65, 60);
        $high = $this->makePrediction('1x2', 'away', 85, 90);

        $picks = (new SurePickService())->compute(10);

        $this->assertSame($high->id, $picks->first()->id);
        $this->assertSame($low->id, $picks->last()->id);
    }
}
