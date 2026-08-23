<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\LeagueMarketGate;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use App\Services\Prediction\Admin\AuditLogger;
use App\Services\Prediction\Admin\PredictionPublicationService;
use Database\Seeders\PredictionCategorySeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionPublicationServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected League $league;
    protected PredictionModel $model;

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
            'prediction_enabled' => true,
        ]);

        $this->model = PredictionModel::create([
            'name' => 'Ensemble',
            'version' => 'v1.0.0',
            'active' => true,
        ]);
    }

    protected function service(): PredictionPublicationService
    {
        return new PredictionPublicationService(new AuditLogger());
    }

    protected function makePrediction(array $overrides = []): Prediction
    {
        return Prediction::create(array_merge([
            'fixture_id' => null,
            'league_id' => 39,
            'market_code' => 'over_1_5',
            'selection' => 'over_1_5',
            'probability' => 80,
            'calibrated_probability' => 80,
            'confidence' => 85,
            'data_quality_score' => 80,
            'model_id' => $this->model->id,
            'model_version' => 'v1.0.0',
            'status' => 'generated',
        ], $overrides));
    }

    public function test_publishes_when_gate_passes(): void
    {
        $prediction = $this->makePrediction();

        $this->service()->apply($prediction);

        $prediction->refresh();
        $this->assertSame('published', $prediction->status);
        $this->assertSame('passed gate', $prediction->publication_reason);
        $this->assertNotNull($prediction->gate_probability);
        $this->assertNotNull($prediction->configuration_version);
    }

    public function test_no_bet_when_probability_below_gate(): void
    {
        $prediction = $this->makePrediction(['probability' => 50, 'calibrated_probability' => 50]);

        $this->service()->apply($prediction);

        $this->assertSame('no_bet', $prediction->fresh()->status);
        $this->assertSame('probability below gate', $prediction->fresh()->publication_reason);
    }

    public function test_no_bet_when_confidence_below_gate(): void
    {
        $prediction = $this->makePrediction(['confidence' => 40]);

        $this->service()->apply($prediction);

        $this->assertSame('no_bet', $prediction->fresh()->status);
        $this->assertSame('confidence below gate', $prediction->fresh()->publication_reason);
    }

    public function test_rejected_when_league_disabled(): void
    {
        $this->league->update(['enabled' => false]);
        $prediction = $this->makePrediction();

        $this->service()->apply($prediction);

        $this->assertSame('rejected', $prediction->fresh()->status);
        $this->assertSame('league disabled', $prediction->fresh()->publication_reason);
    }

    public function test_rejected_when_market_disabled(): void
    {
        PredictionCategory::where('code', 'over_1_5')->update(['enabled' => false]);
        $prediction = $this->makePrediction();

        $this->service()->apply($prediction);

        $this->assertSame('rejected', $prediction->fresh()->status);
        $this->assertSame('market disabled', $prediction->fresh()->publication_reason);
    }

    public function test_rejected_when_model_not_active(): void
    {
        $this->model->update(['active' => false]);
        $prediction = $this->makePrediction();

        $this->service()->apply($prediction);

        $this->assertSame('rejected', $prediction->fresh()->status);
        $this->assertSame('model not active', $prediction->fresh()->publication_reason);
    }

    public function test_league_market_gate_has_highest_precedence(): void
    {
        // Global market: over_1_5 default gate. League x market override: 90/90.
        LeagueMarketGate::create([
            'league_id' => 39,
            'market_code' => 'over_1_5',
            'enabled' => true,
            'min_probability' => 90,
            'min_confidence' => 90,
        ]);

        // Probability 80 passes the global 70/75 gate but fails the 90/90 override.
        $prediction = $this->makePrediction(['probability' => 80, 'calibrated_probability' => 80, 'confidence' => 85]);

        $this->service()->apply($prediction);

        $this->assertSame('no_bet', $prediction->fresh()->status);
        $this->assertSame('probability below gate', $prediction->fresh()->publication_reason);
        $this->assertSame(90, $prediction->fresh()->gate_probability);
    }

    public function test_league_market_disabled_is_rejected(): void
    {
        LeagueMarketGate::create([
            'league_id' => 39,
            'market_code' => 'over_1_5',
            'enabled' => false,
        ]);

        $prediction = $this->makePrediction();

        $this->service()->apply($prediction);

        $this->assertSame('rejected', $prediction->fresh()->status);
        $this->assertSame('league x market disabled', $prediction->fresh()->publication_reason);
    }

    public function test_league_level_probability_override_falls_back_from_market(): void
    {
        // No market-level probability; league-level probability = 85.
        PredictionCategory::where('code', 'over_1_5')->update(['min_probability' => null]);
        $this->league->update(['prediction_min_probability' => 85]);

        $prediction = $this->makePrediction(['probability' => 80, 'calibrated_probability' => 80]);

        $this->service()->apply($prediction);

        $this->assertSame('no_bet', $prediction->fresh()->status);
        $this->assertSame(85, $prediction->fresh()->gate_probability);
    }
}
