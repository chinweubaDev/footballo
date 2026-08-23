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
 * Phase 1L — live pipeline safety: shadow gate bypass, lock immutability and
 * duplicate prevention.
 */
class LivePipelineSafetyTest extends TestCase
{
    use InteractsWithPredictionSchema;
    use PredictionTestHelpers;

    protected PredictionModel $v100;
    protected PredictionModel $v110;
    protected Fixture $fixture;

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
        ]);

        $this->v100 = PredictionModel::updateOrCreate(['version' => 'v1.0.0'], ['name' => 'v1.0.0', 'active' => true]);
        $this->v110 = PredictionModel::create(['name' => 'v1.1.0', 'version' => 'v1.1.0', 'active' => false]);

        $this->fixture = Fixture::create([
            'api_fixture_id' => 9101,
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

    public function test_shadow_generation_bypasses_publication_gate(): void
    {
        // v1.1.0 has a very strict calibration that keeps probabilities low,
        // yet shadow predictions must still be recorded (status = shadow).
        $this->v110->update([
            'configuration' => ['calibration' => ['1x2' => ['method' => 'platt', 'a' => 0.01, 'b' => -2.0]]],
        ]);

        $this->engine()->generate($this->fixture, $this->v110, 'shadow');

        $shadow = Prediction::where('fixture_id', $this->fixture->id)->where('model_version', 'v1.1.0')->get();

        $this->assertNotEmpty($shadow);
        foreach ($shadow as $p) {
            $this->assertSame('shadow', $p->status);
        }
    }

    public function test_locked_prediction_is_not_modified_by_regeneration(): void
    {
        $engine = $this->engine();

        $engine->generate($this->fixture, $this->v100);

        $before = Prediction::where('fixture_id', $this->fixture->id)->where('model_version', 'v1.0.0')->first();
        $before->update(['locked_at' => now(), 'probability' => 99]);

        // Regenerate — locked prediction must remain untouched.
        $engine->generate($this->fixture, $this->v100);

        $after = $before->fresh();
        $this->assertSame('99.00', $after->probability);
    }

    public function test_generation_is_idempotent(): void
    {
        $engine = $this->engine();

        $engine->generate($this->fixture, $this->v100);
        $first = Prediction::where('fixture_id', $this->fixture->id)->where('model_version', 'v1.0.0')->count();

        $engine->generate($this->fixture, $this->v100);
        $second = Prediction::where('fixture_id', $this->fixture->id)->where('model_version', 'v1.0.0')->count();

        $this->assertSame($first, $second);
        $this->assertGreaterThan(0, $first);
    }
}
