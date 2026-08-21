<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use Database\Seeders\LeagueSeeder;
use Database\Seeders\PredictionCategorySeeder;
use Database\Seeders\PredictionModelSeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionSeedersTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_league_seeder_creates_all_six_leagues(): void
    {
        $this->seed(LeagueSeeder::class);

        $this->assertSame(6, League::count());

        $expected = [
            ['name' => 'Premier League', 'id' => 39, 'slug' => 'premier-league'],
            ['name' => 'La Liga', 'id' => 140, 'slug' => 'la-liga'],
            ['name' => 'Serie A', 'id' => 135, 'slug' => 'serie-a'],
            ['name' => 'Bundesliga', 'id' => 78, 'slug' => 'bundesliga'],
            ['name' => 'Ligue 1', 'id' => 61, 'slug' => 'ligue-1'],
            ['name' => 'Eredivisie', 'id' => 88, 'slug' => 'eredivisie'],
        ];

        foreach ($expected as $league) {
            $record = League::where('api_football_league_id', $league['id'])->first();
            $this->assertNotNull($record, "Missing league: {$league['name']}");
            $this->assertSame($league['name'], $record->name);
            $this->assertSame($league['slug'], $record->slug);
            $this->assertTrue($record->enabled);
            $this->assertTrue($record->prediction_enabled);
            $this->assertSame(75, $record->prediction_min_confidence);
        }
    }

    public function test_league_seeder_is_idempotent(): void
    {
        $this->seed(LeagueSeeder::class);
        $this->seed(LeagueSeeder::class);

        $this->assertSame(6, League::count());
    }

    public function test_category_seeder_creates_all_categories_with_defaults(): void
    {
        $this->seed(PredictionCategorySeeder::class);

        $this->assertSame(7, PredictionCategory::count());

        $codes = ['1x2', 'over_1_5', 'over_2_5', 'double_chance', 'btts', 'draw', 'correct_score'];
        foreach ($codes as $code) {
            $this->assertSame(1, PredictionCategory::where('code', $code)->count(), "Missing category code: {$code}");
        }

        $this->assertTrue(PredictionCategory::where('code', '1x2')->first()->enabled);
        $this->assertTrue(PredictionCategory::where('code', '1x2')->first()->homepage_enabled);
        $this->assertTrue(PredictionCategory::where('code', 'double_chance')->first()->homepage_enabled);
        $this->assertFalse(PredictionCategory::where('code', 'over_2_5')->first()->homepage_enabled);

        // Correct Score must have a stricter confidence threshold.
        $this->assertSame(85, PredictionCategory::where('code', 'correct_score')->first()->min_confidence);
    }

    public function test_model_seeder_creates_v1_0_0(): void
    {
        $this->seed(PredictionModelSeeder::class);

        $model = PredictionModel::where('version', 'v1.0.0')->first();
        $this->assertNotNull($model);
        $this->assertSame('Esurebet Statistical Ensemble', $model->name);
        $this->assertTrue($model->active);
    }
}
