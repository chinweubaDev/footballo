<?php

namespace App\Services\Prediction\Evaluation;

/**
 * Pure statistics for resolved predictions. Operates on plain arrays so the
 * same math serves both live Predictions and BacktestPredictions.
 *
 * A "row" is an associative array with at least:
 *   result   => 'won' | 'lost' | 'void'
 *   probability => float (0-100)
 *   confidence  => int (0-100)
 *   market_code => string
 *   league_id   => int|null
 *   model_version => string
 *   data_quality_score => int|null
 *
 * Metrics:
 *   - Accuracy          won / (won + lost)           [void excluded]
 *   - Brier score       mean((p - y)^2), p in [0,1]  [lower is better]
 *   - Log loss          -mean(y*log(p)+(1-y)*log(1-p)) [clipped, lower is better]
 *   - Calibration       predicted probability vs actual success frequency
 *   - Confidence buckets, probability buckets
 *   - Selectivity       all vs 70+ vs 80+ vs 90+ confidence
 */
class MetricsCalculator
{
    /**
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    public function summarize(array $rows): array
    {
        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
        $void = count(array_filter($rows, fn ($r) => ($r['result'] ?? null) === 'void'));
        $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
        $lost = count($resolved) - $won;

        return [
            'total' => count($rows),
            'resolved' => count($resolved),
            'won' => $won,
            'lost' => $lost,
            'void' => $void,
            'accuracy' => $this->accuracy($won, $lost),
            'brier_score' => $this->brierScore($resolved),
            'log_loss' => $this->logLoss($resolved),
            'calibration_error' => $this->expectedCalibrationError($resolved),
            'avg_probability' => $this->average($resolved, 'probability'),
            'avg_confidence' => $this->average($resolved, 'confidence'),
        ];
    }

    /**
     * Expected calibration error (ECE), in percentage points (0-100, lower
     * better). Buckets predicted probabilities and compares the mean predicted
     * probability against the observed success rate, weighted by bucket size.
     *
     * Self-contained on purpose: it must not call probabilityBuckets(), which
     * itself calls summarize() (avoiding infinite recursion).
     *
     * @param list<array<string,mixed>> $resolved
     */
    public function expectedCalibrationError(array $resolved): ?float
    {
        if (empty($resolved)) {
            return null;
        }

        $edges = config('evaluation.probability_buckets', [50, 60, 70, 80, 90, 100]);
        $total = count($resolved);
        $ece = 0.0;

        for ($i = 0; $i < count($edges) - 1; $i++) {
            $low = $edges[$i];
            $high = $edges[$i + 1];
            $inclusiveHigh = $i === count($edges) - 2;

            $bucket = array_values(array_filter($resolved, function ($r) use ($low, $high, $inclusiveHigh) {
                $v = (float) ($r['probability'] ?? 0);

                return $inclusiveHigh ? ($v >= $low && $v <= $high) : ($v >= $low && $v < $high);
            }));

            $n = count($bucket);

            if ($n === 0) {
                continue;
            }

            $won = count(array_filter($bucket, fn ($r) => ($r['result'] ?? null) === 'won'));
            $accuracy = $won / $n * 100;
            $avgProbability = array_sum(array_map(fn ($r) => (float) ($r['probability'] ?? 0), $bucket)) / $n;

            $ece += ($n / $total) * abs($avgProbability - $accuracy);
        }

        return round($ece, 2);
    }

    /**
     * Accuracy over binary outcomes (void excluded).
     */
    public function accuracy(int $won, int $lost): ?float
    {
        $denominator = $won + $lost;

        return $denominator > 0 ? round($won / $denominator * 100, 2) : null;
    }

    /**
     * Brier score: mean((predicted_probability - actual_outcome)^2).
     * Lower is better; 0 is a perfect score, 1 is the worst possible.
     * Only meaningful for binary events.
     *
     * @param list<array<string,mixed>> $resolved
     */
    public function brierScore(array $resolved): ?float
    {
        if (empty($resolved)) {
            return null;
        }

        $sum = 0.0;

        foreach ($resolved as $row) {
            $p = max(0.0, min(1.0, (float) ($row['probability'] ?? 0) / 100.0));
            $y = ($row['result'] === 'won') ? 1.0 : 0.0;
            $sum += ($p - $y) ** 2;
        }

        return round($sum / count($resolved), 4);
    }

    /**
     * Binary cross-entropy (log loss). Probabilities are clipped into
     * [epsilon, 1 - epsilon] to protect against log(0). Lower is better.
     *
     * @param list<array<string,mixed>> $resolved
     */
    public function logLoss(array $resolved): ?float
    {
        if (empty($resolved)) {
            return null;
        }

        $epsilon = (float) config('evaluation.log_loss.epsilon', 1e-12);
        $sum = 0.0;

        foreach ($resolved as $row) {
            $p = max(0.0, min(1.0, (float) ($row['probability'] ?? 0) / 100.0));
            $p = max($epsilon, min(1.0 - $epsilon, $p));
            $y = ($row['result'] === 'won') ? 1.0 : 0.0;
            $sum += $y * log($p) + (1.0 - $y) * log(1.0 - $p);
        }

        return round(-$sum / count($resolved), 4);
    }

    /**
     * Confidence performance buckets (e.g. 50-59, 60-69, ... 90-100).
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function confidenceBuckets(array $rows): array
    {
        return $this->buckets($rows, 'confidence', config('evaluation.confidence_buckets', [50, 60, 70, 80, 90, 100]));
    }

    /**
     * Probability calibration buckets: compare predicted probability against
     * the actual observed success frequency.
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function probabilityBuckets(array $rows): array
    {
        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));

        return $this->buckets($resolved, 'probability', config('evaluation.probability_buckets', [50, 60, 70, 80, 90, 100]));
    }

    /**
     * Selectivity analysis: does raising the confidence bar actually help?
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    public function selectivity(array $rows): array
    {
        $tiers = [
            'all' => 0,
            '70+' => 70,
            '80+' => 80,
            '90+' => 90,
        ];

        $result = [];

        foreach ($tiers as $label => $min) {
            $filtered = array_values(array_filter(
                $rows,
                fn ($r) => ($r['confidence'] ?? 0) >= $min
            ));

            $summary = $this->summarize($filtered);

            $result[$label] = [
                'confidence_min' => $min,
                'total' => $summary['total'],
                'won' => $summary['won'],
                'lost' => $summary['lost'],
                'void' => $summary['void'],
                'accuracy' => $summary['accuracy'],
                'brier_score' => $summary['brier_score'],
            ];
        }

        return $result;
    }

    /**
     * Group rows by market code and summarize.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    public function byMarket(array $rows): array
    {
        return $this->groupBy($rows, 'market_code');
    }

    /**
     * Group rows by league id and summarize.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<int,array<string,mixed>>
     */
    public function byLeague(array $rows): array
    {
        return $this->groupBy($rows, 'league_id');
    }

    /**
     * Group rows by model version and summarize.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    public function byModelVersion(array $rows): array
    {
        return $this->groupBy($rows, 'model_version');
    }

    /**
     * @param list<array<string,mixed>> $rows
     */
    protected function groupBy(array $rows, string $key): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $value = $value === null ? 'unknown' : (string) $value;
            $groups[$value][] = $row;
        }

        $result = [];

        foreach ($groups as $value => $groupRows) {
            $result[$value] = $this->summarize($groupRows);
        }

        return $result;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @param list<int> $edges
     * @return list<array<string,mixed>>
     */
    protected function buckets(array $rows, string $key, array $edges): array
    {
        $buckets = [];

        for ($i = 0; $i < count($edges) - 1; $i++) {
            $low = $edges[$i];
            $high = $edges[$i + 1];
            $inclusiveHigh = $i === count($edges) - 2;

            $bucketRows = array_values(array_filter($rows, function ($r) use ($key, $low, $high, $inclusiveHigh) {
                $value = (float) ($r[$key] ?? 0);

                return $inclusiveHigh
                    ? ($value >= $low && $value <= $high)
                    : ($value >= $low && $value < $high);
            }));

            $summary = $this->summarize($bucketRows);

            $buckets[] = [
                'label' => $inclusiveHigh ? "{$low}-{$high}" : "{$low}-".($high - 1),
                'min' => $low,
                'max' => $high,
                'total' => $summary['total'],
                'won' => $summary['won'],
                'lost' => $summary['lost'],
                'void' => $summary['void'],
                'accuracy' => $summary['accuracy'],
                'avg_probability' => $summary['avg_probability'],
                'avg_confidence' => $summary['avg_confidence'],
                'brier_score' => $summary['brier_score'],
            ];
        }

        return $buckets;
    }

    /**
     * @param list<array<string,mixed>> $rows
     */
    protected function average(array $rows, string $key): ?float
    {
        if (empty($rows)) {
            return null;
        }

        $values = array_map(fn ($r) => (float) ($r[$key] ?? 0), $rows);

        return round(array_sum($values) / count($values), 2);
    }
}
