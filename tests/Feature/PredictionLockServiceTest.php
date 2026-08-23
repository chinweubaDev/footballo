<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Services\Prediction\Admin\PredictionLockService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionLockServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function makeFixture(\DateTimeInterface $kickoff): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => Fixture::max('api_fixture_id') + 1,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'status' => 'NS',
            'match_date' => $kickoff,
        ]);
    }

    protected function makePrediction(Fixture $fixture, string $status = 'published'): Prediction
    {
        return Prediction::create([
            'fixture_id' => $fixture->id,
            'league_id' => 39,
            'market_code' => 'over_1_5',
            'selection' => 'over_1_5',
            'probability' => 75,
            'confidence' => 80,
            'model_version' => 'v1.0.0',
            'status' => $status,
        ]);
    }

    public function test_locks_predictions_within_window(): void
    {
        $soon = $this->makeFixture(now()->addMinutes(10));
        $later = $this->makeFixture(now()->addHours(5));

        $p1 = $this->makePrediction($soon);
        $p2 = $this->makePrediction($later);

        $locked = (new PredictionLockService())->lockDuePredictions(30);

        $this->assertSame(1, $locked);
        $this->assertNotNull($p1->fresh()->locked_at);
        $this->assertNull($p2->fresh()->locked_at);
    }

    public function test_lock_is_idempotent(): void
    {
        $fixture = $this->makeFixture(now()->addMinutes(5));
        $prediction = $this->makePrediction($fixture);

        $first = (new PredictionLockService())->lockDuePredictions(30);
        $second = (new PredictionLockService())->lockDuePredictions(30);

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertNotNull($prediction->fresh()->locked_at);
    }

    public function test_does_not_lock_no_bet_or_settled(): void
    {
        $fixture = $this->makeFixture(now()->addMinutes(5));
        $this->makePrediction($fixture, 'no_bet');

        $locked = (new PredictionLockService())->lockDuePredictions(30);

        $this->assertSame(0, $locked);
    }
}
