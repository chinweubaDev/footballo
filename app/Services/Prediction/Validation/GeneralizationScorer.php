<?php

namespace App\Services\Prediction\Validation;

/**
 * Composite model evaluation score (Phase 1G).
 *
 * Accuracy is deliberately NOT the whole score. The score blends:
 *
 *   score = 0.30 * accuracy_norm
 *         + 0.25 * (1 - brier)
 *         + 0.20 * calibration
 *         + 0.15 * coverage
 *         + 0.10 * consistency
 *
 * where:
 *   accuracy_norm = accuracy / 100            (0..1)
 *   brier         = mean Brier score          (0..1, lower better)
 *   calibration   = 1 - expected_calibration_error / 100
 *   coverage      = coverage fraction         (0..1)
 *   consistency   = 1 - stddev(per-league accuracy) / 100, clamped to 0..1
 *
 * All weights are configurable in config('evaluation.generalization').
 */
class GeneralizationScorer
{
    /**
     * Score a single market across leagues.
     *
     * @param list<array{accuracy:?float,brier_score:?float,calibration_error:?float,coverage:?float,resolved:int}> $leagueRows
     */
    public function score(array $leagueRows, ?int $minimumSample = null): ?array
    {
        $minimumSample ??= (int) config('evaluation.minimum_sample_size', 100);

        $rows = array_values(array_filter($leagueRows, fn ($r) => ($r['resolved'] ?? 0) >= $minimumSample));

        if (empty($rows)) {
            return null;
        }

        $w = config('evaluation.generalization', []);

        $accuracies = array_map(fn ($r) => (float) ($r['accuracy'] ?? 0), $rows);
        $meanAccuracy = array_sum($accuracies) / count($accuracies);

        $briers = array_filter(array_map(fn ($r) => $r['brier_score'] ?? null, $rows), fn ($v) => $v !== null);
        $meanBrier = count($briers) ? array_sum($briers) / count($briers) : 0.5;

        $eces = array_filter(array_map(fn ($r) => $r['calibration_error'] ?? null, $rows), fn ($v) => $v !== null);
        $meanEce = count($eces) ? array_sum($eces) / count($eces) : 0.0;

        $coverages = array_filter(array_map(fn ($r) => $r['coverage'] ?? null, $rows), fn ($v) => $v !== null);
        $meanCoverage = count($coverages) ? array_sum($coverages) / count($coverages) / 100.0 : 0.0;

        $stdAccuracy = $this->stddev($accuracies);

        $accuracyNorm = $meanAccuracy / 100.0;
        $brierTerm = 1.0 - min(1.0, max(0.0, $meanBrier));
        $calibrationTerm = 1.0 - min(1.0, max(0.0, $meanEce / 100.0));
        $coverageTerm = min(1.0, max(0.0, $meanCoverage));
        $consistencyTerm = 1.0 - min(1.0, max(0.0, $stdAccuracy / 100.0));

        $score = ($w['accuracy_weight'] ?? 0.30) * $accuracyNorm
            + ($w['brier_weight'] ?? 0.25) * $brierTerm
            + ($w['calibration_weight'] ?? 0.20) * $calibrationTerm
            + ($w['coverage_weight'] ?? 0.15) * $coverageTerm
            + ($w['consistency_weight'] ?? 0.10) * $consistencyTerm;

        return [
            'score' => round($score * 100, 2),
            'mean_accuracy' => round($meanAccuracy, 2),
            'median_accuracy' => round($this->median($accuracies), 2),
            'std_accuracy' => round($stdAccuracy, 2),
            'min_accuracy' => round(min($accuracies), 2),
            'max_accuracy' => round(max($accuracies), 2),
            'mean_brier' => round($meanBrier, 4),
            'mean_calibration_error' => round($meanEce, 2),
            'mean_coverage' => round($meanCoverage * 100, 2),
            'leagues_evaluated' => count($rows),
        ];
    }

    protected function stddev(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);
        $sumSq = 0.0;

        foreach ($values as $v) {
            $sumSq += ($v - $mean) ** 2;
        }

        return sqrt($sumSq / (count($values) - 1));
    }

    protected function median(array $values): float
    {
        sort($values);
        $n = count($values);

        if ($n === 0) {
            return 0.0;
        }

        $mid = intdiv($n, 2);

        return $n % 2 === 1 ? $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }
}
