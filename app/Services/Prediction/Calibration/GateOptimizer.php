<?php

namespace App\Services\Prediction\Calibration;

use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Publication gate optimizer (Phase 1G.1).
 *
 * Sweeps a 2D grid of (probability × confidence) thresholds per market and
 * measures accuracy, coverage, Brier, average probability/confidence and a
 * Wilson 95% confidence interval for every combination. It then derives a
 * RECOMMENDED gate using a documented composite rule — accuracy is never the
 * only criterion — and NEVER applies it automatically.
 *
 * Rows are plain arrays:
 *   market_code, probability (0-100), confidence (0-100), result (won|lost|void)
 */
class GateOptimizer
{
    public const INSUFFICIENT_SAMPLE = 'INSUFFICIENT SAMPLE';
    public const LOW_SAMPLE = 'LOW SAMPLE';
    public const SUFFICIENT_SAMPLE = 'SUFFICIENT SAMPLE';

    public const STATUS_CURRENT = 'CURRENT';
    public const STATUS_PROMISING = 'PROMISING';
    public const STATUS_WEAK = 'WEAK';
    public const STATUS_INSUFFICIENT_DATA = 'INSUFFICIENT_DATA';

    public function __construct(
        protected MetricsCalculator $metrics,
        protected CalibrationReportService $reports,
    ) {
    }

    /**
     * Per-market 2D threshold grid.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,list<array<string,mixed>>>
     */
    public function grid(array $rows): array
    {
        $cfg = config('evaluation.gate_optimizer', []);
        $byMarket = $this->groupByMarket($rows);

        $result = [];

        foreach ($byMarket as $market => $marketRows) {
            $result[$market] = $this->gridMarket($marketRows, $cfg);
        }

        return $result;
    }

    /**
     * Per-market recommended gate + market status.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    public function recommend(array $rows): array
    {
        $cfg = config('evaluation.gate_optimizer', []);
        $byMarket = $this->groupByMarket($rows);

        $result = [];

        foreach ($byMarket as $market => $marketRows) {
            $grid = $this->gridMarket($marketRows, $cfg);
            $result[$market] = $this->recommendFromGrid($grid, $marketRows, $cfg);
        }

        return $result;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @param array<string,mixed> $cfg
     * @return list<array<string,mixed>>
     */
    protected function gridMarket(array $rows, array $cfg): array
    {
        $probThresholds = $cfg['probability_thresholds'] ?? [50, 55, 60, 65, 70, 75, 80, 85, 90];
        $confThresholds = $cfg['confidence_thresholds'] ?? [50, 55, 60, 65, 70, 75, 80, 85, 90];
        $insufficient = (int) ($cfg['insufficient_sample_threshold'] ?? 50);
        $minimum = (int) ($cfg['minimum_sample_size'] ?? 100);

        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
        $total = count($resolved);

        $out = [];

        foreach ($probThresholds as $prob) {
            foreach ($confThresholds as $conf) {
                $passed = array_values(array_filter($resolved, fn ($r) =>
                    (float) ($r['probability'] ?? 0) >= $prob && (int) ($r['confidence'] ?? 0) >= $conf));

                $summary = $this->metrics->summarize($passed);
                $won = $summary['won'];
                $n = $summary['resolved'];
                $half = $this->reports->wilsonHalfWidth($won, $n);

                $out[] = [
                    'min_probability' => $prob,
                    'min_confidence' => $conf,
                    'predictions' => $n,
                    'wins' => $won,
                    'losses' => $summary['lost'],
                    'accuracy' => $summary['accuracy'],
                    'coverage_percent' => $total > 0 ? round($n / $total * 100, 2) : null,
                    'brier_score' => $summary['brier_score'],
                    'avg_probability' => $summary['avg_probability'],
                    'avg_confidence' => $summary['avg_confidence'],
                    'ci_lower' => $summary['accuracy'] !== null && $half !== null ? round($summary['accuracy'] - $half * 100, 2) : null,
                    'ci_upper' => $summary['accuracy'] !== null && $half !== null ? round($summary['accuracy'] + $half * 100, 2) : null,
                    'sample_label' => $this->sampleLabel($n, $insufficient, $minimum),
                ];
            }
        }

        return $out;
    }

    /**
     * @param list<array<string,mixed>> $grid
     * @param list<array<string,mixed>> $rows
     * @param array<string,mixed> $cfg
     * @return array<string,mixed>
     */
    protected function recommendFromGrid(array $grid, array $rows, array $cfg): array
    {
        $minimum = (int) ($cfg['minimum_sample_size'] ?? 100);
        $minCoverage = (float) ($cfg['minimum_coverage'] ?? 10.0);
        $minAccuracy = (float) ($cfg['minimum_accuracy'] ?? 60.0);
        $maxBrier = (float) ($cfg['max_brier'] ?? 0.30);
        $weights = $cfg['weights'] ?? ['accuracy' => 0.45, 'brier' => 0.30, 'coverage' => 0.25];

        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));

        if (count($resolved) < $minimum) {
            return $this->recommendation(null, count($resolved), 'Insufficient data to optimize the gate.');
        }

        $candidates = array_values(array_filter($grid, fn ($p) =>
            ($p['predictions'] ?? 0) >= $minimum
            && ($p['coverage_percent'] ?? 0) >= $minCoverage
            && ($p['accuracy'] ?? 0) >= $minAccuracy
            && $p['brier_score'] !== null
            && $p['brier_score'] <= $maxBrier));

        if (empty($candidates)) {
            return $this->recommendation(null, count($resolved), 'No threshold pair meets the sample/coverage/accuracy/Brier floors.');
        }

        $best = null;
        $bestScore = -1.0;

        foreach ($candidates as $candidate) {
            $score = ($weights['accuracy'] ?? 0.45) * ($candidate['accuracy'] / 100)
                + ($weights['brier'] ?? 0.30) * (1 - $candidate['brier_score'])
                + ($weights['coverage'] ?? 0.25) * ($candidate['coverage_percent'] / 100);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        $reason = sprintf(
            'Selected min_probability=%d, min_confidence=%d (score=%.3f, %d candidates) from the documented composite rule.',
            $best['min_probability'],
            $best['min_confidence'],
            round($bestScore, 3),
            count($candidates),
        );

        return $this->recommendation($best, count($resolved), $reason);
    }

    /**
     * @param array<string,mixed>|null $best
     * @return array<string,mixed>
     */
    protected function recommendation(?array $best, int $resolved, string $reason): array
    {
        return [
            'recommended_min_probability' => $best['min_probability'] ?? null,
            'recommended_min_confidence' => $best['min_confidence'] ?? null,
            'accuracy' => $best['accuracy'] ?? null,
            'coverage_percent' => $best['coverage_percent'] ?? null,
            'predictions' => $best['predictions'] ?? 0,
            'brier_score' => $best['brier_score'] ?? null,
            'ci_lower' => $best['ci_lower'] ?? null,
            'ci_upper' => $best['ci_upper'] ?? null,
            'resolved' => $resolved,
            'reason' => $reason,
        ];
    }

    /**
     * Derive a market status from measured results (never hardcoded).
     * Expects rows for a SINGLE market.
     *
     * @param list<array<string,mixed>> $rows
     */
    public function marketStatus(array $rows): string
    {
        $cfg = config('evaluation.gate_optimizer', []);
        $minimum = (int) ($cfg['minimum_sample_size'] ?? 100);

        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));

        if (count($resolved) < $minimum) {
            return self::STATUS_INSUFFICIENT_DATA;
        }

        $grid = $this->gridMarket($rows, $cfg);
        $recommendation = $this->recommendFromGrid($grid, $rows, $cfg);

        if ($recommendation['recommended_min_probability'] === null) {
            return self::STATUS_WEAK;
        }

        $strong = (float) config('evaluation.status_classification.strong_accuracy', 62.0);

        return ($recommendation['accuracy'] ?? 0) >= $strong ? self::STATUS_CURRENT : self::STATUS_PROMISING;
    }

    protected function sampleLabel(int $n, int $insufficient, int $minimum): string
    {
        if ($n < $insufficient) {
            return self::INSUFFICIENT_SAMPLE;
        }

        if ($n < $minimum) {
            return self::LOW_SAMPLE;
        }

        return self::SUFFICIENT_SAMPLE;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array<string,list<array<string,mixed>>>
     */
    protected function groupByMarket(array $rows): array
    {
        $byMarket = [];

        foreach ($rows as $row) {
            $byMarket[$row['market_code'] ?? 'unknown'][] = $row;
        }

        return $byMarket;
    }
}
