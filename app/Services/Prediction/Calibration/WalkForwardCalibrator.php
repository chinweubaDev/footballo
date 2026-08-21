<?php

namespace App\Services\Prediction\Calibration;

use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Trains and evaluates per-market probability calibration using a strict
 * chronological train/validation split (walk-forward). Calibration is only
 * ever fit on data BEFORE the validation period, so no future information
 * leaks into the calibration model.
 */
class WalkForwardCalibrator
{
    /** Markets that are treated as binary calibration targets. */
    public const BINARY_MARKETS = ['1x2', 'double_chance', 'draw', 'over_1_5', 'over_2_5', 'btts'];

    /** Minimum training samples per market before a calibrator is fit. */
    public const MIN_TRAIN_SAMPLES = 30;

    public function __construct(protected MetricsCalculator $metrics)
    {
    }

    /**
     * @param list<array{market_code:string,probability:float,result:string,t:string}> $rows
     * @return array<string,mixed>
     */
    public function fitAndEvaluate(array $rows, float $trainFraction = 0.7): array
    {
        $rows = $this->sortChronologically($rows);

        $binary = array_values(array_filter($rows, function ($r) {
            return in_array($r['result'], ['won', 'lost'], true)
                && in_array($r['market_code'], self::BINARY_MARKETS, true);
        }));

        $n = count($binary);
        $trainCount = (int) floor($n * $trainFraction);

        $train = array_slice($binary, 0, $trainCount);
        $validation = array_slice($binary, $trainCount);

        $models = [];
        $perMarket = [];

        foreach (self::BINARY_MARKETS as $market) {
            $trainRows = array_values(array_filter($train, fn ($r) => $r['market_code'] === $market));
            $valRows = array_values(array_filter($validation, fn ($r) => $r['market_code'] === $market));

            if (count($trainRows) < self::MIN_TRAIN_SAMPLES) {
                $perMarket[$market] = [
                    'trained' => false,
                    'method' => null,
                    'train_count' => count($trainRows),
                    'validation_count' => count($valRows),
                    'reason' => 'insufficient_training_data',
                ];
                continue;
            }

            $probs = array_values(array_map(fn ($r) => (float) $r['probability'], $trainRows));
            $outcomes = array_values(array_map(fn ($r) => $r['result'] === 'won' ? 1 : 0, $trainRows));

            // Primary method: Platt scaling (low-variance, robust on small data).
            $model = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::PLATT);

            // Comparison method: isotonic regression (reported, not selected).
            $iso = (new ProbabilityCalibrator())->fit($probs, $outcomes, ProbabilityCalibrator::ISOTONIC);

            $models[$market] = $model;

            $perMarket[$market] = [
                'trained' => true,
                'method' => ProbabilityCalibrator::PLATT,
                'train_count' => count($trainRows),
                'validation_count' => count($valRows),
                'raw_brier' => $this->metrics->brierScore($this->toRows($valRows)),
                'platt_brier' => $this->metrics->brierScore($this->toRows($valRows, $model)),
                'isotonic_brier' => $this->metrics->brierScore($this->toRows($valRows, $iso)),
                'raw_ece' => $this->expectedCalibrationError($valRows),
                'platt_ece' => $this->expectedCalibrationError($valRows, $model),
                'parameters' => $model->parameters(),
            ];
        }

        return [
            'train_count' => $trainCount,
            'validation_count' => count($validation),
            'train_fraction' => $trainFraction,
            'models' => $models,
            'per_market' => $perMarket,
        ];
    }

    /**
     * Apply fitted calibration models to resolved rows.
     *
     * @param array<string,ProbabilityCalibrator> $models
     * @param list<array{market_code:string,probability:float,result:string}> $rows
     * @return list<array{market_code:string,probability:float,result:string}>
     */
    public function apply(array $models, array $rows): array
    {
        return array_map(function ($row) use ($models) {
            $market = $row['market_code'];

            if (isset($models[$market]) && $row['market_code'] !== 'correct_score') {
                $row['probability'] = $models[$market]->predict((float) $row['probability']);
            }

            return $row;
        }, $rows);
    }

    /**
     * Sort rows chronologically by their timestamp.
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    protected function sortChronologically(array $rows): array
    {
        usort($rows, fn ($a, $b) => strcmp((string) ($a['t'] ?? ''), (string) ($b['t'] ?? '')));

        return $rows;
    }

    /**
     * @param list<array{market_code:string,probability:float,result:string}> $rows
     * @return list<array<string,mixed>>
     */
    protected function toRows(array $rows, ?ProbabilityCalibrator $model = null): array
    {
        return array_map(function ($row) use ($model) {
            $p = (float) $row['probability'];

            if ($model !== null) {
                $p = $model->predict($p);
            }

            return [
                'market_code' => $row['market_code'],
                'probability' => $p,
                'result' => $row['result'],
            ];
        }, $rows);
    }

    /**
     * Expected calibration error: weighted mean of |avg_predicted - actual|
     * across probability buckets.
     *
     * @param list<array{market_code:string,probability:float,result:string}> $rows
     */
    protected function expectedCalibrationError(array $rows, ?ProbabilityCalibrator $model = null): float
    {
        $calibrated = $this->toRows($rows, $model);

        $buckets = $this->metrics->probabilityBuckets($calibrated);

        $weighted = 0.0;
        $total = 0;

        foreach ($buckets as $bucket) {
            if ($bucket['total'] === 0 || $bucket['accuracy'] === null || $bucket['avg_probability'] === null) {
                continue;
            }

            $weighted += $bucket['total'] * abs($bucket['avg_probability'] - $bucket['accuracy']);
            $total += $bucket['total'];
        }

        return $total > 0 ? round($weighted / $total, 4) : 0.0;
    }
}
