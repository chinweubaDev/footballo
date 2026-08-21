<?php

namespace App\Services\Prediction\Calibration;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;

/**
 * Produces the calibration and data-quality reports used by the admin
 * dashboard. These reports MEASURE the model — they never tune it.
 */
class CalibrationReportService
{
    /**
     * Wilson 95% confidence interval half-width for a success rate.
     */
    public function wilsonHalfWidth(int $wins, int $total, float $z = 1.96): ?float
    {
        if ($total <= 0) {
            return null;
        }

        $p = $wins / $total;
        $denom = 1 + ($z * $z) / $total;

        $center = ($p + ($z * $z) / (2 * $total)) / $denom;
        $margin = $z * sqrt(($p * (1 - $p)) / $total + ($z * $z) / (4 * $total * $total)) / $denom;

        return round($margin, 4);
    }

    /**
     * Feature availability / coverage report (Phase 1F data-quality analysis).
     *
     * @return array<string,mixed>
     */
    public function dataQualityReport(?BacktestRun $run = null): array
    {
        $run ??= BacktestRun::query()->where('status', BacktestRun::STATUS_COMPLETED)->latest('id')->first();

        $features = [
            ['feature' => 'Team form', 'available' => true, 'source' => 'stored fixtures', 'coverage' => 100],
            ['feature' => 'Team stats (goals for/against)', 'available' => true, 'source' => 'stored fixtures', 'coverage' => 100],
            ['feature' => 'Home/away split', 'available' => true, 'source' => 'stored fixtures', 'coverage' => 100],
            ['feature' => 'Head-to-head', 'available' => true, 'source' => 'stored fixtures', 'coverage' => 100],
            ['feature' => 'Standings', 'available' => true, 'source' => 'stored fixtures', 'coverage' => 100],
            ['feature' => 'Historical odds', 'available' => false, 'source' => 'not stored', 'coverage' => 0],
            ['feature' => 'API-Football AI prediction', 'available' => false, 'source' => 'not stored', 'coverage' => 0],
            ['feature' => 'Injuries', 'available' => false, 'source' => 'not stored', 'coverage' => 0],
            ['feature' => 'Lineups', 'available' => false, 'source' => 'not stored', 'coverage' => 0],
        ];

        $distribution = null;

        if ($run) {
            $scores = BacktestPrediction::where('backtest_run_id', $run->id)
                ->whereNotNull('data_quality_score')
                ->pluck('data_quality_score');

            $distribution = [
                'count' => $scores->count(),
                'min' => $scores->min(),
                'max' => $scores->max(),
                'avg' => $scores->count() ? round($scores->avg(), 1) : null,
            ];
        }

        return [
            'features' => $features,
            'distribution' => $distribution,
            'note' => 'Odds, API-AI, injuries and lineups are unavailable in historical backtests because they are not stored; this caps the data-quality score (max 60 of 100).',
        ];
    }

    /**
     * Confidence-bucket accuracy with 95% confidence intervals.
     *
     * @param list<array{confidence:int,result:string}> $rows
     * @return list<array<string,mixed>>
     */
    public function confidenceBuckets(array $rows): array
    {
        $edges = config('evaluation.confidence_buckets', [50, 60, 70, 80, 90, 100]);
        $out = [];

        for ($i = 0; $i < count($edges) - 1; $i++) {
            $low = $edges[$i];
            $high = $edges[$i + 1];
            $inclusiveHigh = $i === count($edges) - 2;

            $bucketRows = array_values(array_filter($rows, function ($r) use ($low, $high, $inclusiveHigh) {
                $v = (float) ($r['confidence'] ?? 0);

                return $inclusiveHigh ? ($v >= $low && $v <= $high) : ($v >= $low && $v < $high);
            }));

            $resolved = array_values(array_filter($bucketRows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
            $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
            $lost = count($resolved) - $won;

            $accuracy = count($resolved) > 0 ? round($won / count($resolved) * 100, 2) : null;
            $half = $this->wilsonHalfWidth($won, count($resolved));

            $out[] = [
                'label' => $inclusiveHigh ? "{$low}-{$high}" : "{$low}-".($high - 1),
                'predictions' => count($resolved),
                'wins' => $won,
                'losses' => $lost,
                'accuracy' => $accuracy,
                'ci_lower' => $accuracy !== null && $half !== null ? round($accuracy - $half * 100, 2) : null,
                'ci_upper' => $accuracy !== null && $half !== null ? round($accuracy + $half * 100, 2) : null,
            ];
        }

        return $out;
    }
}
