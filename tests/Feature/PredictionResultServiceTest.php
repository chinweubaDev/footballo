<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\Evaluation\MarketResultResolver;
use App\Services\Prediction\Evaluation\PredictionResultService;
use App\Services\Prediction\Admin\AuditLogger;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionResultServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function service(): PredictionResultService
    {
        return new PredictionResultService(new MarketResultResolver(), new AuditLogger());
    }

    protected function makeFixture(string $status, ?int $homeGoals, ?int $awayGoals): Fixture
    {
        return Fixture::create([
            'api_fixture_id' => Fixture::max('api_fixture_id') + 1,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'status' => $status,
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'match_date' => now()->subDay(),
        ]);
    }

    protected function makePrediction(Fixture $fixture, string $market, string $selection, ?string $adminSelection = null): Prediction
    {
        return Prediction::create([
            'fixture_id' => $fixture->id,
            'league_id' => 39,
            'market_code' => $market,
            'selection' => $selection,
            'probability' => 70,
            'confidence' => 80,
            'model_version' => 'v1.0.0',
            'status' => 'published',
            'admin_selection' => $adminSelection,
        ]);
    }

    public function test_resolves_1x2_won(): void
    {
        $fixture = $this->makeFixture('FT', 2, 1);
        $prediction = $this->makePrediction($fixture, '1x2', 'home');

        $this->assertSame('won', $this->service()->resolvePrediction($prediction, $fixture));

        $prediction->refresh();
        $this->assertSame('won', $prediction->result);
        $this->assertSame('won', $prediction->model_result);
        $this->assertSame('2-1', $prediction->actual_score);
        $this->assertNotNull($prediction->resolved_at);
    }

    public function test_resolves_multi_market(): void
    {
        $fixture = $this->makeFixture('FT', 2, 1);
        $over15 = $this->makePrediction($fixture, 'over_1_5', 'over_1_5');
        $over25 = $this->makePrediction($fixture, 'over_2_5', 'over_2_5');
        $btts = $this->makePrediction($fixture, 'btts', 'yes');

        $this->assertSame('won', $this->service()->resolvePrediction($over15, $fixture));
        $this->assertSame('won', $this->service()->resolvePrediction($over25, $fixture));
        $this->assertSame('won', $this->service()->resolvePrediction($btts, $fixture));
    }

    public function test_void_on_postponed_fixture(): void
    {
        $fixture = $this->makeFixture('PST', null, null);
        $prediction = $this->makePrediction($fixture, '1x2', 'home');

        $this->assertSame('void', $this->service()->resolvePrediction($prediction, $fixture));

        $prediction->refresh();
        $this->assertSame('void', $prediction->result);
        $this->assertSame('PST', $prediction->void_reason);
    }

    public function test_pending_on_not_started_fixture(): void
    {
        $fixture = $this->makeFixture('NS', null, null);
        $prediction = $this->makePrediction($fixture, '1x2', 'home');

        $this->assertSame('pending', $this->service()->resolvePrediction($prediction, $fixture));
        $this->assertNull($prediction->fresh()->result);
    }

    public function test_result_immutability_records_correction(): void
    {
        $fixture = $this->makeFixture('FT', 2, 1);
        $prediction = $this->makePrediction($fixture, '1x2', 'home');

        $this->service()->resolvePrediction($prediction, $fixture);
        $this->assertSame('won', $prediction->fresh()->result);

        // API-Football corrects the official result to 1-1 (draw).
        $fixture->update(['home_goals' => 1, 'away_goals' => 1]);
        $this->service()->resolvePrediction($prediction->fresh(), $fixture->fresh());

        $prediction->refresh();
        $this->assertSame('lost', $prediction->result); // home no longer wins
        $corrections = $prediction->result_corrections;
        $this->assertIsArray($corrections);
        $this->assertNotEmpty($corrections);
        $this->assertSame('won', $corrections[0]['previous_result']);
        $this->assertSame('lost', $corrections[0]['new_result']);
    }

    public function test_resolve_completed_fixtures_is_idempotent(): void
    {
        $fixture = $this->makeFixture('FT', 1, 0);
        $this->makePrediction($fixture, '1x2', 'home');

        $first = $this->service()->resolveCompletedFixtures();
        $second = $this->service()->resolveCompletedFixtures();

        $this->assertSame(1, $first['predictions']);
        $this->assertSame(0, $second['predictions']); // no duplicate resolution
        $this->assertSame('won', Prediction::first()->result);
    }
}
