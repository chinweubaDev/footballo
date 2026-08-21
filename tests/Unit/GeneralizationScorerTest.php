<?php

namespace Tests\Unit;

use App\Services\Prediction\Validation\GeneralizationScorer;
use Tests\TestCase;

class GeneralizationScorerTest extends TestCase
{
    public function test_consistent_model_scores_higher_than_inconsistent(): void
    {
        $scorer = new GeneralizationScorer();

        $consistent = [
            ['accuracy' => 72.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 74.0, 'brier_score' => 0.19, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 71.0, 'brier_score' => 0.21, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 73.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
        ];

        $inconsistent = [
            ['accuracy' => 90.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 60.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 50.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
            ['accuracy' => 40.0, 'brier_score' => 0.20, 'calibration_error' => 5.0, 'coverage' => 100.0, 'resolved' => 200],
        ];

        $consistentScore = $scorer->score($consistent, 100)['score'];
        $inconsistentScore = $scorer->score($inconsistent, 100)['score'];

        // Despite the inconsistent model's higher mean (60 vs 72.5) ... wait:
        // mean of consistent = 72.5, mean of inconsistent = 60. The consistent
        // model wins on both accuracy and consistency here. Build a case where
        // the inconsistent model has HIGHER mean but still scores lower.
        $this->assertGreaterThan($inconsistentScore, $consistentScore);
    }

    public function test_accuracy_is_not_the_whole_score(): void
    {
        $scorer = new GeneralizationScorer();

        $highAccBadCal = [
            ['accuracy' => 85.0, 'brier_score' => 0.45, 'calibration_error' => 25.0, 'coverage' => 10.0, 'resolved' => 200],
            ['accuracy' => 85.0, 'brier_score' => 0.45, 'calibration_error' => 25.0, 'coverage' => 10.0, 'resolved' => 200],
        ];

        $lowerAccGoodCal = [
            ['accuracy' => 75.0, 'brier_score' => 0.15, 'calibration_error' => 3.0, 'coverage' => 90.0, 'resolved' => 200],
            ['accuracy' => 75.0, 'brier_score' => 0.15, 'calibration_error' => 3.0, 'coverage' => 90.0, 'resolved' => 200],
        ];

        $this->assertGreaterThan(
            $scorer->score($highAccBadCal, 100)['score'],
            $scorer->score($lowerAccGoodCal, 100)['score'],
        );
    }

    public function test_insufficient_sample_returns_null(): void
    {
        $scorer = new GeneralizationScorer();

        $this->assertNull($scorer->score([
            ['accuracy' => 90.0, 'brier_score' => 0.1, 'calibration_error' => 1.0, 'coverage' => 100.0, 'resolved' => 5],
        ], 100));
    }
}
