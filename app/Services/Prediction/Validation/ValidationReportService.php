<?php

namespace App\Services\Prediction\Validation;

use App\Models\BacktestPrediction;
use App\Models\League;
use App\Services\Prediction\Calibration\CalibrationReportService;
use App\Services\Prediction\Calibration\ThresholdOptimizer;
use App\Services\Prediction\Evaluation\DataDriftService;
use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Builds the multi-league / multi-season validation report (Phase 1G) from
 * resolved backtest predictions. Values always carry their sample size.
 */
class ValidationReportService
{
    protected const MARKETS = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];

    public function __construct(
        protected MetricsCalculator $metrics,
        protected CalibrationReportService $reports,
        protected GeneralizationScorer $scorer,
        protected StatusClassificationService $status,
        protected DataDriftService $drift,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function report(): array
    {
        $matrix = $this->leagueMarketMatrix();

        return [
            'matrix' => $matrix,
            'markets' => $this->marketSummary($matrix),
            'leagues' => $this->leagueSummary($matrix),
            'ranked_combinations' => $this->rankedCombinations($matrix),
            'generalization' => $this->generalizationScores($matrix),
            'data_drift' => $this->drift->detect(),
            'minimum_sample_size' => (int) config('evaluation.minimum_sample_size', 100),
        ];
    }

    /**
     * League × market matrix with Wilson confidence intervals.
     *
     * @return array<int,array<string,mixed>>
     */
    public function leagueMarketMatrix(): array
    {
        $leagueNames = League::query()->pluck('name', 'api_football_league_id')->all();

        $rows = BacktestPrediction::query()
            ->with('fixture:id,league_id')
            ->whereNotNull('result')
            ->get(['backtest_run_id', 'fixture_id', 'market_code', 'probability', 'confidence', 'result', 'status']);

        $matrix = [];

        foreach ($rows as $p) {
            $league = $p->fixture?->league_id ?? 'unknown';
            $market = $p->market_code ?? 'unknown';

            $matrix[$league][$market][] = $p;
        }

        $result = [];

        foreach ($matrix as $leagueId => $markets) {
            $leagueResult = [
                'league_id' => $leagueId,
                'league_name' => $leagueNames[$leagueId] ?? "League {$leagueId}",
                'markets' => [],
            ];

            foreach (self::MARKETS as $market) {
                if (! isset($markets[$market])) {
                    continue;
                }

                $marketRows = array_map(fn ($p) => [
                    'market_code' => $p->market_code,
                    'probability' => (float) $p->probability,
                    'confidence' => (int) $p->confidence,
                    'result' => $p->result,
                    'status' => $p->status,
                ], $markets[$market]);

                $bettable = array_values(array_filter($marketRows, fn ($r) => ($r['status'] ?? null) !== 'no_bet'));
                $summary = $this->metrics->summarize($bettable);
                $half = $this->reports->wilsonHalfWidth($summary['won'], $summary['resolved']);

                $leagueResult['markets'][$market] = array_merge($summary, [
                    'market_code' => $market,
                    'ci_lower' => $summary['accuracy'] !== null && $half !== null ? round($summary['accuracy'] - $half * 100, 2) : null,
                    'ci_upper' => $summary['accuracy'] !== null && $half !== null ? round($summary['accuracy'] + $half * 100, 2) : null,
                ]);
            }

            $result[] = $leagueResult;
        }

        return $result;
    }

    /**
     * Per-market summary across leagues (mean/median/std/min/max accuracy).
     *
     * @param list<array<string,mixed>> $matrix
     * @return array<string,array<string,mixed>>
     */
    public function marketSummary(array $matrix): array
    {
        $out = [];

        foreach (self::MARKETS as $market) {
            $rows = [];

            foreach ($matrix as $league) {
                $m = $league['markets'][$market] ?? null;

                if ($m && $m['resolved'] > 0 && $m['accuracy'] !== null) {
                    $rows[] = $m;
                }
            }

            if (empty($rows)) {
                continue;
            }

            $out[$market] = $this->scorer->score(array_map(fn ($m) => [
                'accuracy' => $m['accuracy'],
                'brier_score' => $m['brier_score'],
                'calibration_error' => $m['calibration_error'] ?? null,
                'coverage' => 100.0,
                'resolved' => $m['resolved'],
            ], $rows));

            $out[$market]['total_resolved'] = array_sum(array_map(fn ($m) => $m['resolved'], $rows));
            $totalWon = array_sum(array_map(fn ($m) => $m['won'], $rows));
            $out[$market]['market_code'] = $market;
            $out[$market]['status'] = $this->status->classify([
                'resolved' => $out[$market]['total_resolved'],
                'accuracy' => $out[$market]['total_resolved'] > 0
                    ? round($totalWon / $out[$market]['total_resolved'] * 100, 2)
                    : null,
                'brier_score' => $out[$market]['mean_brier'] ?? null,
            ]);
        }

        return $out;
    }

    /**
     * Per-league overall summary.
     *
     * @param list<array<string,mixed>> $matrix
     * @return list<array<string,mixed>>
     */
    public function leagueSummary(array $matrix): array
    {
        $out = [];

        foreach ($matrix as $league) {
            $all = [];

            foreach ($league['markets'] as $m) {
                $all[] = $m;
            }

            $totalResolved = array_sum(array_map(fn ($m) => $m['resolved'], $all));
            $totalWon = array_sum(array_map(fn ($m) => $m['won'], $all));

            $briers = array_filter(array_map(fn ($m) => $m['brier_score'] ?? null, $all), fn ($v) => $v !== null);
            $meanBrier = count($briers) ? array_sum($briers) / count($briers) : null;

            $summary = [
                'league_id' => $league['league_id'],
                'league_name' => $league['league_name'],
                'markets' => count($league['markets']),
                'resolved' => $totalResolved,
                'accuracy' => $totalResolved > 0 ? round($totalWon / $totalResolved * 100, 2) : null,
                'brier_score' => $meanBrier === null ? null : round($meanBrier, 4),
            ];

            $out[] = $this->status->withStatus($summary);
        }

        return $out;
    }

    /**
     * Ranked league × market combinations (strongest evidence first).
     *
     * @param list<array<string,mixed>> $matrix
     * @return list<array<string,mixed>>
     */
    public function rankedCombinations(array $matrix): array
    {
        $minimum = (int) config('evaluation.minimum_sample_size', 100);
        $combos = [];

        foreach ($matrix as $league) {
            foreach ($league['markets'] as $market => $m) {
                $combo = [
                    'league' => $league['league_name'],
                    'league_id' => $league['league_id'],
                    'market' => $market,
                    'accuracy' => $m['accuracy'],
                    'resolved' => $m['resolved'],
                    'brier' => $m['brier_score'],
                    'calibration_error' => $m['calibration_error'] ?? null,
                    'ci_lower' => $m['ci_lower'],
                    'ci_upper' => $m['ci_upper'],
                    'sufficient' => $m['resolved'] >= $minimum,
                ];

                $combo['status'] = $this->status->classify([
                    'resolved' => $m['resolved'],
                    'accuracy' => $m['accuracy'],
                    'brier_score' => $m['brier_score'],
                ]);

                $combos[] = $combo;
            }
        }

        usort($combos, function ($a, $b) {
            // Sufficient-sample combos first, then by accuracy desc.
            if ($a['sufficient'] !== $b['sufficient']) {
                return $a['sufficient'] ? -1 : 1;
            }

            return ($b['accuracy'] ?? 0) <=> ($a['accuracy'] ?? 0);
        });

        return $combos;
    }

    /**
     * @param list<array<string,mixed>> $matrix
     * @return array<string,array<string,mixed>>
     */
    public function generalizationScores(array $matrix): array
    {
        return $this->marketSummary($matrix);
    }
}
