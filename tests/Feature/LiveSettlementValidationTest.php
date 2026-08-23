<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\Prediction\Evaluation\MarketResultResolver;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use App\Services\Prediction\Evaluation\PredictionResultService;
use App\Services\Prediction\Admin\AuditLogger;
use App\Services\Prediction\FeatureProvenanceService;
use App\Services\SystemEventService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1M §2/§3/§31/§32 — live settlement integrity:
 * provenance marking, provenance-exclusion from performance, and
 * SETTLEMENT_PENDING_REVIEW for ambiguous scorelines.
 */
class LiveSettlementValidationTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    protected function service(): PredictionResultService
    {
        return new PredictionResultService(new MarketResultResolver(), new AuditLogger(), new FeatureProvenanceService());
    }

    protected function makeFixture(array $overrides = []): Fixture
    {
        return Fixture::create(array_merge([
            'api_fixture_id' => Fixture::max('api_fixture_id') + 1,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'home_team_id' => 100,
            'away_team_id' => 200,
            'status' => 'FT',
            'home_goals' => 2,
            'away_goals' => 1,
            'match_date' => now()->subHour(),
        ], $overrides));
    }

    protected function makePrediction(Fixture $fixture, array $overrides = []): Prediction
    {
        return Prediction::create(array_merge([
            'fixture_id' => $fixture->id,
            'league_id' => 39,
            'market_code' => '1x2',
            'selection' => 'home',
            'probability' => 70,
            'confidence' => 80,
            'model_version' => 'v1.0.0',
            'status' => 'published',
            'prediction_generated_at' => now()->subHours(3),
            'feature_data_timestamp' => now()->subHours(4),
        ], $overrides));
    }

    public function test_settlement_records_provenance_and_settlement_status(): void
    {
        $fixture = $this->makeFixture();
        $prediction = $this->makePrediction($fixture);

        $this->assertSame('won', $this->service()->resolvePrediction($prediction, $fixture));

        $prediction->refresh();
        $this->assertSame('valid', $prediction->provenance_status);
        $this->assertSame('settled', $prediction->settlement_status);
        $this->assertSame('2-1', $prediction->actual_score);
    }

    public function test_generated_after_kickoff_is_provenance_invalid_but_still_auditable(): void
    {
        $fixture = $this->makeFixture(['match_date' => now()->subHours(5)]);
        $prediction = $this->makePrediction($fixture, [
            'prediction_generated_at' => now()->subHour(),   // AFTER kickoff
            'feature_data_timestamp' => now()->subHours(2),
        ]);

        $this->service()->resolvePrediction($prediction, $fixture);

        $prediction->refresh();
        $this->assertSame('invalid', $prediction->provenance_status);
        $this->assertSame('settled', $prediction->settlement_status); // still settled for audit
        $this->assertNotNull($prediction->result);
    }

    public function test_invalid_provenance_is_excluded_from_performance(): void
    {
        $fixture = $this->makeFixture(); // FT 2-1 (home win)

        // Valid provenance, home -> WON.
        $good = $this->makePrediction($fixture);

        // Invalid provenance (generated after kickoff), away -> LOST. Excluded.
        $bad = $this->makePrediction($fixture, [
            'selection' => 'away',
            'prediction_generated_at' => now()->subHour(),
            'feature_data_timestamp' => now()->subHours(2),
        ]);

        $this->service()->resolvePrediction($good, $fixture);
        $this->service()->resolvePrediction($bad, $fixture);

        $overview = (new PerformanceAnalyticsService(new MetricsCalculator()))->dashboard()['overview'];

        // Only the valid prediction counts toward accuracy.
        $this->assertSame(1, $overview['won']);
        $this->assertSame(0, $overview['lost']);
        $this->assertSame(100.0, $overview['accuracy']);
    }

    public function test_ambiguous_score_marks_pending_review_and_does_not_settle(): void
    {
        $fixture = $this->makeFixture(['home_goals' => -1, 'away_goals' => 2]);
        $prediction = $this->makePrediction($fixture);

        $this->assertSame('pending', $this->service()->resolvePrediction($prediction, $fixture));

        $prediction->refresh();
        $this->assertNull($prediction->result);
        $this->assertSame('pending_review', $prediction->settlement_status);
    }

    public function test_ambiguous_score_creates_system_event(): void
    {
        $fixture = $this->makeFixture(['home_goals' => -1, 'away_goals' => 2]);
        $prediction = $this->makePrediction($fixture);

        $service = new PredictionResultService(new MarketResultResolver(), new AuditLogger(), new FeatureProvenanceService(), new SystemEventService());
        $service->resolvePrediction($prediction, $fixture);

        $this->assertDatabaseHas('system_events', ['type' => 'ambiguous_result']);
    }

    public function test_result_correction_preserves_original_settlement(): void
    {
        $fixture = $this->makeFixture();
        $prediction = $this->makePrediction($fixture);

        $this->service()->resolvePrediction($prediction, $fixture); // home wins 2-1
        $this->assertSame('won', $prediction->fresh()->result);

        // API later corrects the result: 2-2 (draw) -> home now loses.
        $fixture->update(['home_goals' => 2, 'away_goals' => 2]);
        $this->service()->resolvePrediction($prediction->fresh(), $fixture->fresh());

        $prediction->refresh();
        $this->assertSame('lost', $prediction->result);
        $this->assertNotNull($prediction->result_corrections);
        $this->assertCount(1, $prediction->result_corrections);
        $this->assertSame('won', $prediction->result_corrections[0]['previous_result']);
        $this->assertSame('lost', $prediction->result_corrections[0]['new_result']);
    }
}
