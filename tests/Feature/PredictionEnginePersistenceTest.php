<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionFeature;
use App\Models\PredictionModel;
use App\Services\Prediction\DataCollector;
use Database\Seeders\PredictionCategorySeeder;
use Mockery;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

class PredictionEnginePersistenceTest extends TestCase
{
    use InteractsWithPredictionSchema;
    use PredictionTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        PredictionModel::updateOrCreate(
            ['version' => 'v1.0.0'],
            [
                'name' => 'Esurebet Statistical Ensemble',
                'active' => true,
            ],
        );

        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'prediction_min_confidence' => 75,
            'auto_publish' => true,
        ]);
    }

    public function test_generate_persists_per_market_without_duplicates(): void
    {
        $fixture = Fixture::create([
            'api_fixture_id' => 12345,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'match_date' => now(),
        ]);

        $collector = Mockery::mock(DataCollector::class);
        $collector->shouldReceive('collect')->andReturn($this->makeContext());

        $engine = $this->makePredictionEngine($collector);

        $engine->generate($fixture);
        $engine->generate($fixture); // re-run must not create duplicates

        $predictions = Prediction::where('fixture_id', $fixture->id)->get();

        $this->assertSame(7, $predictions->count());
        $this->assertSame(1, PredictionFeature::where('fixture_id', $fixture->id)->count());

        foreach ($predictions as $prediction) {
            $this->assertContains($prediction->status, ['published', 'no_bet']);
            $this->assertSame('v1.0.0', $prediction->model_version);
            $this->assertSame(39, (int) $prediction->league_id);
        }

        // Correct score is inherently low-probability and must be NO_BET.
        $this->assertSame('no_bet', $predictions->firstWhere('market_code', 'correct_score')->status);

        // Over 1.5 is high-probability and must publish.
        $this->assertSame('published', $predictions->firstWhere('market_code', 'over_1_5')->status);

        // Legacy fields are still populated for backward compatibility.
        $oneXTwo = $predictions->firstWhere('market_code', '1x2');
        $this->assertNotEmpty($oneXTwo->tip);
        $this->assertNotEmpty($oneXTwo->category);
        $this->assertNotNull($oneXTwo->confidence);
    }
}
