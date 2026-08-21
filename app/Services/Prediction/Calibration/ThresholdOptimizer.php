<?php

namespace App\Services\Prediction\Calibration;

use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Sweeps candidate probability thresholds and reports the accuracy/coverage
 * tradeoff for each market. Thresholds are NEVER auto-applied; this only
 * surfaces evidence for an admin to make an informed decision.
 */
class ThresholdOptimizer
{
    public const THRESHOLDS = [60, 65, 70, 75, 80, 85, 90];

    public function __construct(protected MetricsCalculator $metrics)
    {
    }

    /**
     * @param list<array{market_code:string,probability:float,result:string}> $rows resolved rows (won/lost/void)
     * @return array<string,list<array<string,mixed>>>
     */
    public function sweep(array $rows, ?int $minimumSampleSize = null): array
    {
        $minimumSampleSize ??= (int) config('evaluation.minimum_sample_size', 100);

        $byMarket = [];

        foreach ($rows as $row) {
            $byMarket[$row['market_code'] ?? 'unknown'][] = $row;
        }

        $result = [];

        foreach ($byMarket as $market => $marketRows) {
            $result[$market] = $this->sweepMarket($marketRows, $minimumSampleSize);
        }

        return $result;
    }

    /**
     * @param list<array{probability:float,result:string}> $rows
     * @return list<array<string,mixed>>
     */
    protected function sweepMarket(array $rows, int $minimumSampleSize): array
    {
        $out = [];

        foreach (self::THRESHOLDS as $threshold) {
            $passed = array_values(array_filter($rows, fn ($r) => (float) $r['probability'] >= $threshold));
            $summary = $this->metrics->summarize($passed);

            $out[] = [
                'threshold' => $threshold,
                'predictions' => $summary['resolved'],
                'wins' => $summary['won'],
                'losses' => $summary['lost'],
                'accuracy' => $summary['accuracy'],
                'brier_score' => $summary['brier_score'],
                'coverage_percent' => $this->coverage($passed, $rows),
                'insufficient_sample' => $summary['resolved'] < $minimumSampleSize,
            ];
        }

        return $out;
    }

    /**
     * Coverage = fraction of the market's resolved predictions that pass the
     * threshold.
     *
     * @param list<array<string,mixed>> $passed
     * @param list<array<string,mixed>> $all
     */
    protected function coverage(array $passed, array $all): float
    {
        if (empty($all)) {
            return 0.0;
        }

        return round(count($passed) / count($all) * 100, 2);
    }
}
