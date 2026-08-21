<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\Fixture;

/**
 * Tracks the distribution of league-level features over time and flags DATA
 * DRIFT when a window's value moves materially relative to the baseline
 * (Phase 1G §38). This never retrains the model — it only warns an admin.
 *
 * Tracked features (computed purely from stored fixtures — never fabricated):
 *   - league scoring rate (average total goals per match)
 *   - home-win rate (% of matches won by the home side)
 *   - over-2.5 rate (% of matches with 3+ goals)
 *   - BTTS rate (% of matches where both teams scored)
 *
 * Expected goals (xG) is intentionally NOT tracked here because historical xG
 * is not stored in this database (see CalibrationReportService).
 */
class DataDriftService
{
    /**
     * @return array<string,mixed>
     */
    public function detect(?int $leagueId = null): array
    {
        $cfg = config('evaluation.data_drift', []);
        $windowMonths = (int) ($cfg['window_months'] ?? 3);
        $minFixtures = (int) ($cfg['minimum_fixtures'] ?? 30);
        $thresholdPct = (float) ($cfg['drift_threshold_pct'] ?? 5.0);

        $fixtures = Fixture::query()
            ->whereIn('status', config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']))
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->whereNotNull('match_date')
            ->when($leagueId > 0, fn ($q) => $q->where('league_id', $leagueId))
            ->orderBy('match_date')
            ->get(['league_id', 'match_date', 'home_goals', 'away_goals']);

        if ($fixtures->isEmpty()) {
            return $this->emptyResult($leagueId, $windowMonths, $minFixtures, $thresholdPct);
        }

        $windows = [];

        foreach ($fixtures as $fixture) {
            $label = $this->windowLabel($fixture->match_date, $windowMonths);
            $windows[$label][] = $fixture;
        }

        ksort($windows);

        $result = [
            'league_id' => $leagueId,
            'window_months' => $windowMonths,
            'minimum_fixtures' => $minFixtures,
            'drift_threshold_pct' => $thresholdPct,
            'drift_detected' => false,
            'flags' => [],
            'windows' => [],
        ];

        $baseline = null;

        foreach ($windows as $label => $rows) {
            $count = count($rows);

            $metrics = $this->windowMetrics($rows);

            $window = [
                'label' => $label,
                'fixtures' => $count,
                'measurable' => $count >= $minFixtures,
                'league_scoring_rate' => $metrics['league_scoring_rate'],
                'home_win_rate' => $metrics['home_win_rate'],
                'over_25_rate' => $metrics['over_25_rate'],
                'btts_rate' => $metrics['btts_rate'],
                'drifted_metrics' => [],
            ];

            if ($baseline === null && $count >= $minFixtures) {
                $baseline = $metrics;
                $baseline['label'] = $label;
                $baseline['fixtures'] = $count;
            }

            if ($baseline !== null && $count >= $minFixtures) {
                foreach (array_keys($metrics) as $metric) {
                    if ($this->drifted($metrics[$metric], $baseline[$metric], $thresholdPct)) {
                        $window['drifted_metrics'][] = $metric;
                    }
                }

                if (! empty($window['drifted_metrics'])) {
                    $result['drift_detected'] = true;
                    $result['flags'] = array_values(array_unique(array_merge($result['flags'], $window['drifted_metrics'])));
                }
            }

            $result['windows'][] = $window;
        }

        if ($baseline !== null) {
            $result['baseline'] = $baseline;
        }

        return $result;
    }

    /**
     * @param list<Fixture> $rows
     * @return array<string,float>
     */
    protected function windowMetrics(array $rows): array
    {
        $total = count($rows);

        $goals = 0;
        $homeWins = 0;
        $over25 = 0;
        $btts = 0;

        foreach ($rows as $fixture) {
            $h = (int) $fixture->home_goals;
            $a = (int) $fixture->away_goals;

            $goals += $h + $a;

            if ($h > $a) {
                $homeWins++;
            }

            if ($h + $a > 2.5) {
                $over25++;
            }

            if ($h > 0 && $a > 0) {
                $btts++;
            }
        }

        return [
            'league_scoring_rate' => $total > 0 ? round($goals / $total, 2) : 0.0,
            'home_win_rate' => $total > 0 ? round($homeWins / $total * 100, 2) : 0.0,
            'over_25_rate' => $total > 0 ? round($over25 / $total * 100, 2) : 0.0,
            'btts_rate' => $total > 0 ? round($btts / $total * 100, 2) : 0.0,
        ];
    }

    /**
     * A metric has drifted when its value moved by more than the configured
     * relative threshold (percentage points) versus the baseline.
     */
    protected function drifted(float $value, float $baseline, float $thresholdPct): bool
    {
        if ($baseline == 0.0) {
            return abs($value) > 0.0;
        }

        $change = abs(($value - $baseline) / $baseline * 100);

        return $change > $thresholdPct;
    }

    protected function windowLabel($date, int $windowMonths): string
    {
        $start = $date->copy()->startOfMonth();
        $bucket = intdiv($start->month - 1, $windowMonths) * $windowMonths + 1;
        $end = $start->copy()->month($bucket)->addMonths($windowMonths - 1)->endOfMonth();

        return $start->format('Y-m').' — '.$end->format('Y-m');
    }

    /**
     * @return array<string,mixed>
     */
    protected function emptyResult(?int $leagueId, int $windowMonths, int $minFixtures, float $thresholdPct): array
    {
        return [
            'league_id' => $leagueId,
            'window_months' => $windowMonths,
            'minimum_fixtures' => $minFixtures,
            'drift_threshold_pct' => $thresholdPct,
            'drift_detected' => false,
            'flags' => [],
            'windows' => [],
            'insufficient_data' => true,
        ];
    }
}
