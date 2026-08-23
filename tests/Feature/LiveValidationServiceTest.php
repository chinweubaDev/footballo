<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Services\Prediction\Evaluation\LiveValidationService;
use App\Services\Prediction\Evaluation\MetricsCalculator;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

/**
 * Phase 1M §6/§7/§8/§9/§10/§16/§17/§18/§34 — live evidence analytics.
 */
class LiveValidationServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        PredictionModel::create(['name' => 'Ensemble v1.0', 'version' => 'v1.0.0', 'active' => true]);
        PredictionModel::create(['name' => 'Ensemble v1.1', 'version' => 'v1.1.0', 'active' => false]);

        // fixture 1: FT 2-1 (home win, 3 goals)
        // fixture 2: FT 0-1 (away win, 1 goal)
        foreach ([1 => [2, 1], 2 => [0, 1]] as $id => [$hg, $ag]) {
            Fixture::create([
                'api_fixture_id' => $id,
                'league_id' => 39,
                'league_name' => 'Premier League',
                'home_team' => "Home{$id}",
                'away_team' => "Away{$id}",
                'status' => 'FT',
                'home_goals' => $hg,
                'away_goals' => $ag,
                'match_date' => now()->subDay(),
            ]);
        }

        // v1.0 home won on f1, lost on f2.
        Prediction::create(['fixture_id' => 1, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 70, 'confidence' => 80, 'model_version' => 'v1.0.0', 'status' => 'published', 'result' => 'won']);
        Prediction::create(['fixture_id' => 2, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 70, 'confidence' => 80, 'model_version' => 'v1.0.0', 'status' => 'published', 'result' => 'lost']);

        // v1.1 away lost on f1, won on f2 (disagrees with v1.0 on both).
        Prediction::create(['fixture_id' => 1, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'away', 'probability' => 65, 'confidence' => 78, 'model_version' => 'v1.1.0', 'status' => 'shadow', 'result' => 'lost']);
        Prediction::create(['fixture_id' => 2, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'away', 'probability' => 65, 'confidence' => 78, 'model_version' => 'v1.1.0', 'status' => 'shadow', 'result' => 'won']);

        // f1 over_2_5: both agree, both won (3 goals).
        Prediction::create(['fixture_id' => 1, 'league_id' => 39, 'market_code' => 'over_2_5', 'selection' => 'over_2_5', 'probability' => 68, 'confidence' => 76, 'model_version' => 'v1.0.0', 'status' => 'published', 'result' => 'won']);
        Prediction::create(['fixture_id' => 1, 'league_id' => 39, 'market_code' => 'over_2_5', 'selection' => 'over_2_5', 'probability' => 66, 'confidence' => 75, 'model_version' => 'v1.1.0', 'status' => 'shadow', 'result' => 'won']);
    }

    protected function service(): LiveValidationService
    {
        return new LiveValidationService(new MetricsCalculator());
    }

    public function test_evidence_labels_follow_configured_thresholds(): void
    {
        $s = $this->service();

        $this->assertSame('INSUFFICIENT', $s->evidenceLabel(0));
        $this->assertSame('INSUFFICIENT', $s->evidenceLabel(49));
        $this->assertSame('PRELIMINARY', $s->evidenceLabel(50));
        $this->assertSame('PRELIMINARY', $s->evidenceLabel(99));
        $this->assertSame('MEANINGFUL', $s->evidenceLabel(100));
        $this->assertSame('MEANINGFUL', $s->evidenceLabel(499));
        $this->assertSame('STRONGER EVIDENCE', $s->evidenceLabel(500));
    }

    public function test_paired_comparison_builds_win_matrix(): void
    {
        $paired = $this->service()->pairedComparison();

        $this->assertSame(3, $paired['paired']);
        $this->assertSame(1, $paired['win_matrix']['both_won']);
        $this->assertSame(1, $paired['win_matrix']['a_won_b_lost']);
        $this->assertSame(1, $paired['win_matrix']['a_lost_b_won']);
        $this->assertSame(0, $paired['win_matrix']['both_lost']);

        // v1.0: won on f1 (1x2 + over2.5), lost on f2 -> 2/3.
        $this->assertSame(2, $paired['a']['won']);
        $this->assertSame(1, $paired['a']['lost']);
        $this->assertSame(66.67, $paired['a']['accuracy']);

        // v1.1: won on f2 + f1 over2.5, lost on f1 1x2 -> 2/3.
        $this->assertSame(2, $paired['b']['won']);
        $this->assertSame(1, $paired['b']['lost']);

        // McNemar: discordant b=1, c=1 -> no difference (p = 1.0).
        $this->assertSame(2, $paired['mcnemar']['discordant']);
        $this->assertSame(1.0, $paired['mcnemar']['p_value']);
        $this->assertFalse($paired['mcnemar']['significant']);
    }

    public function test_model_agreement_measures_same_selection(): void
    {
        $agreement = $this->service()->modelAgreement();

        $markets = collect($agreement['markets'])->keyBy('market_code');

        $this->assertSame(2, $markets['1x2']['pairs']);
        $this->assertSame(0, $markets['1x2']['same_selection']);
        $this->assertSame(0.0, $markets['1x2']['agreement_percent']);

        $this->assertSame(1, $markets['over_2_5']['pairs']);
        $this->assertSame(1, $markets['over_2_5']['same_selection']);
        $this->assertSame(100.0, $markets['over_2_5']['agreement_percent']);

        $this->assertSame(3, $agreement['total_pairs']);
        $this->assertSame(1, $agreement['total_same']);
        $this->assertSame(33.33, $agreement['overall_agreement_percent']);
    }

    public function test_gate_analysis_sweeps_thresholds_without_applying(): void
    {
        $gates = collect($this->service()->gateAnalysis())->keyBy('label');

        // 70/75: v1.0 has 2 qualifying (prob>=70, conf>=75), 1 of them won.
        $this->assertSame(2, $gates['70/75']['models']['v1.0.0']['resolved']);
        $this->assertSame(1, $gates['70/75']['models']['v1.0.0']['won']);
        $this->assertSame(50.0, $gates['70/75']['models']['v1.0.0']['accuracy']);

        // 70/75: v1.1 has no qualifying rows (prob 65/66 < 70).
        $this->assertSame(0, $gates['70/75']['models']['v1.1.0']['resolved']);

        // 60/60: both models have all 3 rows qualifying.
        $this->assertSame(3, $gates['60/60']['models']['v1.0.0']['resolved']);
        $this->assertSame(3, $gates['60/60']['models']['v1.1.0']['resolved']);
    }

    public function test_sure_picks_and_most_featured_evaluation(): void
    {
        Fixture::create([
            'api_fixture_id' => 99,
            'league_id' => 39,
            'league_name' => 'Premier League',
            'home_team' => 'A',
            'away_team' => 'B',
            'status' => 'FT',
            'home_goals' => 1,
            'away_goals' => 0,
            'match_date' => now()->subDay(),
        ]);

        Prediction::create(['fixture_id' => 99, 'league_id' => 39, 'market_code' => '1x2', 'selection' => 'home', 'probability' => 72, 'confidence' => 82, 'model_version' => 'v1.0.0', 'status' => 'published', 'result' => 'won', 'surepick_tip_content' => 'Home win']);

        Prediction::create(['fixture_id' => 99, 'league_id' => 39, 'market_code' => 'double_chance', 'selection' => '1x', 'probability' => 80, 'confidence' => 70, 'model_version' => 'v1.0.0', 'status' => 'published', 'result' => 'won', 'featured' => true]);

        $sure = $this->service()->surePicksEvaluation();
        $this->assertSame(1, $sure['generated']);
        $this->assertSame(1, $sure['published']);
        $this->assertSame(1, $sure['resolved']);
        $this->assertSame(1, $sure['wins']);
        $this->assertSame(100.0, $sure['accuracy']);

        $featured = $this->service()->mostFeaturedEvaluation();
        $this->assertSame(1, $featured['resolved']);
        $this->assertSame(1, $featured['wins']);
    }

    public function test_summary_reports_evidence_per_model(): void
    {
        $summary = $this->service()->summary();

        $this->assertSame(6, $summary['total_resolved']);
        $this->assertArrayHasKey('v1.0.0', $summary['models']);
        $this->assertArrayHasKey('v1.1.0', $summary['models']);
        $this->assertSame('INSUFFICIENT', $summary['models']['v1.0.0']['evidence']);
    }

    public function test_counters_report_prediction_level_totals(): void
    {
        $summary = $this->service()->summary();
        $counters = $summary['counters'];

        $this->assertSame(6, $counters['total_predictions']);
        $this->assertSame(0, $counters['total_settled']);
        $this->assertSame(0, $counters['total_provenance_invalid']);

        $this->assertSame(3, $counters['models']['v1.0.0']['predictions']);
        $this->assertSame(3, $counters['models']['v1.0.0']['published']);
        $this->assertSame(0, $counters['models']['v1.0.0']['no_bet']);
        $this->assertSame(100.0, $counters['models']['v1.0.0']['coverage']);

        // Shadow predictions are never counted as published.
        $this->assertSame(3, $counters['models']['v1.1.0']['predictions']);
        $this->assertSame(0, $counters['models']['v1.1.0']['published']);
    }

    public function test_market_and_league_performance_split_by_model(): void
    {
        $service = $this->service();

        $markets = $service->marketPerformanceByModel();
        $this->assertArrayHasKey('v1.0.0', $markets);
        $this->assertArrayHasKey('v1.1.0', $markets);

        $v10Markets = collect($markets['v1.0.0'])->keyBy('market_code');
        $this->assertSame(2, $v10Markets['1x2']['resolved']);
        $this->assertSame(1, $v10Markets['over_2_5']['resolved']);

        $leagues = $service->leaguePerformanceByModel();
        $this->assertCount(1, $leagues['v1.0.0']);
        $this->assertSame(3, $leagues['v1.0.0'][0]['resolved']);
    }

    public function test_league_market_matrix_split_by_model(): void
    {
        $matrix = $this->service()->leagueMarketMatrixByModel();

        $this->assertCount(1, $matrix['v1.0.0']);
        $league = $matrix['v1.0.0'][0];
        $this->assertArrayHasKey('1x2', $league['markets']);
        $this->assertArrayHasKey('over_2_5', $league['markets']);
        $this->assertSame(2, $league['markets']['1x2']['resolved']);
    }
}
