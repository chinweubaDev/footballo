<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Services\Prediction\DataCollector;
use Database\Seeders\PredictionCategorySeeder;
use Mockery;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\Concerns\PredictionTestHelpers;
use Tests\TestCase;

/**
 * Shadow-mode safety: v1.1.0 predictions are stored under model_version
 * 'v1.1.0' with status 'shadow' and never appear on the public site.
 */
class ShadowModeTest extends TestCase
{
    use InteractsWithPredictionSchema;
    use PredictionTestHelpers;

    protected PredictionModel $v100;
    protected PredictionModel $v110;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);

        League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'homepage_enabled' => true,
            'prediction_min_confidence' => 75,
            'auto_publish' => true,
        ]);

        $this->v100 = PredictionModel::create([
            'name' => 'v1.0.0',
            'version' => 'v1.0.0',
            'active' => true,
        ]);

        $this->v110 = PredictionModel::create([
            'name' => 'v1.1.0',
            'version' => 'v1.1.0',
            'active' => false,
        ]);
    }

    protected function makeFixture(): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => 9001,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'match_date' => now()->addDay(),
        ]);
    }

    protected function engine(): \App\Services\Prediction\PredictionEngine
    {
        $collector = Mockery::mock(DataCollector::class);
        $collector->shouldReceive('collect')->andReturn($this->makeContext());

        return $this->makePredictionEngine($collector);
    }

    public function test_shadow_predictions_are_never_published_publicly(): void
    {
        $fixture = $this->makeFixture();
        $engine = $this->engine();

        $engine->generate($fixture); // live v1.0.0
        $engine->generate($fixture, $this->v110, 'shadow'); // shadow v1.1.0

        $live = Prediction::where('fixture_id', $fixture->id)->where('model_version', 'v1.0.0')->get();
        $shadow = Prediction::where('fixture_id', $fixture->id)->where('model_version', 'v1.1.0')->get();

        $this->assertNotEmpty($live);
        $this->assertNotEmpty($shadow);

        foreach ($live as $p) {
            $this->assertContains($p->status, ['published', 'no_bet']);
        }

        foreach ($shadow as $p) {
            // Shadow predictions are never 'published' publicly.
            $this->assertContains($p->status, ['shadow', 'no_bet']);
        }

        // The public-facing query (status=published) returns only v1.0.0.
        $public = Prediction::where('fixture_id', $fixture->id)->where('status', 'published')->get();
        $this->assertTrue($public->every(fn ($p) => $p->model_version === 'v1.0.0'));
    }

    public function test_v1_1_0_calibration_is_applied_to_shadow_probability(): void
    {
        // Platt a=0.5, b=0 shrinks probabilities toward 50%.
        $this->v110->update([
            'configuration' => [
                'calibration' => [
                    '1x2' => ['method' => 'platt', 'a' => 0.5, 'b' => 0.0],
                ],
            ],
        ]);

        $fixture = $this->makeFixture();
        $engine = $this->engine();

        $engine->generate($fixture);
        $engine->generate($fixture, $this->v110->fresh(), 'shadow');

        $live1x2 = Prediction::where('fixture_id', $fixture->id)->where('model_version', 'v1.0.0')->where('market_code', '1x2')->first();
        $shadow1x2 = Prediction::where('fixture_id', $fixture->id)->where('model_version', 'v1.1.0')->where('market_code', '1x2')->first();

        $this->assertNotNull($live1x2);
        $this->assertNotNull($shadow1x2);

        // Same selection, but the shadow probability is calibrated downward
        // toward 50% (a=0.5 shrinks).
        $this->assertSame($live1x2->selection, $shadow1x2->selection);
        $this->assertLessThan((float) $live1x2->probability, (float) $shadow1x2->probability);
    }
}
