<?php

namespace App\Services\Prediction\Validation;

use App\Models\BacktestRun;
use App\Models\Fixture;
use App\Models\League;
use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Phase 1P — multi-season validation analysis.
 *
 * Pools BACKTEST predictions across seasons (2023/2024/2025) and measures
 * whether market/league strengths generalize. Backtest data is ALWAYS kept
 * separate from live predictions; nothing here touches live or settled data.
 *
 * Pooled accuracy is always total wins / total resolved — never a blind
 * average of per-season percentages.
 */
class MultiSeasonAnalysisService
{
    public function __construct(
        protected ValidationMatrixService $matrix,
        protected MetricsCalculator $metrics,
    ) {
    }

    /**
     * Distinct seasons present in completed backtest runs.
     *
     * @return list<int>
     */
    public function seasons(?string $modelVersion = null): array
    {
        return BacktestRun::query()
            ->where('status', BacktestRun::STATUS_COMPLETED)
            ->when($modelVersion !== null, fn ($q) => $q->where('model_version', $modelVersion))
            ->whereNotNull('season')
            ->distinct()
            ->orderBy('season')
            ->pluck('season')
            ->map(fn ($s) => (int) $s)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Dataset inventory: league × season fixture counts and completeness.
     *
     * @return list<array<string,mixed>>
     */
    public function inventory(): array
    {
        $leagues = League::query()->get(['api_football_league_id', 'name']);

        $totals = Fixture::query()
            ->selectRaw('league_id, season, count(*) as fixtures')
            ->groupBy('league_id', 'season')
            ->get();

        $completed = Fixture::query()
            ->whereIn('status', config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']))
            ->selectRaw('league_id, season, count(*) as n')
            ->groupBy('league_id', 'season')
            ->get()
            ->keyBy(fn ($r) => $r->league_id.'|'.$r->season);

        $out = [];

        foreach ($leagues as $league) {
            foreach ($totals->where('league_id', $league->api_football_league_id) as $r) {
                $total = (int) $r->fixtures;
                $done = (int) ($completed[$r->league_id.'|'.$r->season]->n ?? 0);

                $out[] = [
                    'league_id' => $r->league_id,
                    'league_name' => $league->name,
                    'season' => (int) $r->season,
                    'fixtures' => $total,
                    'completed' => $done,
                    'missing' => max(0, $total - $done),
                    'completeness' => $total > 0 ? round($done / $total * 100, 1) : null,
                ];
            }
        }

        usort($out, fn ($a, $b) => [$a['season'], $a['league_name']] <=> [$b['season'], $b['league_name']]);

        return $out;
    }

    /**
     * Market × Season × Model performance (pooled resolved predictions).
     *
     * @return list<array<string,mixed>>
     */
    public function marketBySeason(?string $modelVersion = null): array
    {
        return $this->groupAndSummarize('market_code', $modelVersion);
    }

    /**
     * League × Season × Model performance (pooled resolved predictions).
     *
     * @return list<array<string,mixed>>
     */
    public function leagueBySeason(?string $modelVersion = null): array
    {
        $rows = $this->resolvedRows($modelVersion);
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $out = $this->summarizeGroups($rows, ['league_id', 'season'], 'model_version');

        foreach ($out as &$row) {
            $row['league_name'] = $names[$row['league_id']] ?? "League {$row['league_id']}";
        }

        return $out;
    }

    /**
     * Market generalization across seasons: per-season accuracy list, pooled
     * accuracy (wins/resolved), mean/median/std/min/max of per-season accuracy.
     *
     * @return list<array<string,mixed>>
     */
    public function marketGeneralization(?string $modelVersion = null): array
    {
        $rows = $this->resolvedRows($modelVersion);
        $groups = [];

        foreach ($rows as $r) {
            $groups[$r['model_version']][$r['market_code']][$r['season']][] = $r;
        }

        $out = [];

        foreach ($groups as $version => $markets) {
            foreach ($markets as $market => $seasons) {
                $perSeason = [];

                foreach ($seasons as $season => $seasonRows) {
                    $summary = $this->metrics->summarize($seasonRows);

                    if ($summary['resolved'] > 0) {
                        $perSeason[] = [
                            'season' => (int) $season,
                            'n' => $summary['resolved'],
                            'wins' => $summary['won'],
                            'losses' => $summary['lost'],
                            'accuracy' => $summary['accuracy'],
                            'brier' => $summary['brier_score'],
                            'ece' => $summary['calibration_error'],
                        ];
                    }
                }

                usort($perSeason, fn ($a, $b) => $a['season'] <=> $b['season']);

                $allRows = array_merge(...array_values($seasons));
                $pooled = $this->metrics->summarize($allRows);

                $out[] = [
                    'model_version' => $version,
                    'market_code' => $market,
                    'pooled' => array_merge($pooled, ['n_seasons' => count($perSeason)]),
                    'per_season' => $perSeason,
                    'generalization' => $this->spread(array_column($perSeason, 'accuracy')),
                ];
            }
        }

        usort($out, fn ($a, $b) => [$a['model_version'], $a['market_code']] <=> [$b['model_version'], $b['market_code']]);

        return $out;
    }

    /**
     * League generalization: pooled performance per league across all seasons.
     *
     * @return list<array<string,mixed>>
     */
    public function leagueGeneralization(?string $modelVersion = null): array
    {
        $rows = $this->resolvedRows($modelVersion);
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $out = $this->summarizeGroups($rows, ['model_version', 'league_id'], null);

        foreach ($out as &$row) {
            $row['league_name'] = $names[$row['league_id']] ?? "League {$row['league_id']}";
        }

        return $out;
    }

    /**
     * League × Market pooled across seasons.
     *
     * @return list<array<string,mixed>>
     */
    public function leagueMarketPooled(?string $modelVersion = null): array
    {
        $rows = $this->resolvedRows($modelVersion);
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $out = $this->summarizeGroups($rows, ['model_version', 'league_id', 'market_code'], null);

        foreach ($out as &$row) {
            $row['league_name'] = $names[$row['league_id']] ?? "League {$row['league_id']}";
        }

        return $out;
    }

    /**
     * Temporal stability: per-market accuracy per season (2023 → 2024 → 2025).
     *
     * @return list<array<string,mixed>>
     */
    public function temporalStability(?string $modelVersion = null): array
    {
        $generalization = $this->marketGeneralization($modelVersion);

        return array_map(fn ($m) => [
            'model_version' => $m['model_version'],
            'market_code' => $m['market_code'],
            'series' => array_map(fn ($s) => $s['accuracy'], $m['per_season']),
            'seasons' => array_map(fn ($s) => $s['season'], $m['per_season']),
            'n' => array_map(fn ($s) => $s['n'], $m['per_season']),
        ], $generalization);
    }

    /**
     * Pooled accuracy helper: total wins / total resolved.
     */
    public function pooledAccuracy(array $rows): ?float
    {
        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));

        if (empty($resolved)) {
            return null;
        }

        $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));

        return round($won / count($resolved) * 100, 2);
    }

    /**
     * Load all resolved backtest rows tagged with season/league/model.
     *
     * @return list<array<string,mixed>>
     */
    public function resolvedRows(?string $modelVersion = null): array
    {
        return $this->matrix->loadRows($this->matrix->canonicalRuns(null, $modelVersion));
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @param list<string> $groupKeys
     * @return list<array<string,mixed>>
     */
    protected function summarizeGroups(array $rows, array $groupKeys, ?string $extraModelKey): array
    {
        $groups = [];

        foreach ($rows as $r) {
            $key = implode('|', array_map(fn ($k) => (string) ($r[$k] ?? 'unknown'), $groupKeys));

            if ($extraModelKey !== null) {
                $key .= '|'.($r[$extraModelKey] ?? 'unknown');
            }

            $groups[$key][] = $r;
        }

        $out = [];

        foreach ($groups as $key => $groupRows) {
            $parts = explode('|', $key);
            $summary = $this->metrics->summarize($groupRows);

            $row = array_merge($summary, [
                'model_version' => $parts[0] ?? 'unknown',
                'n_seasons' => count(array_unique(array_column($groupRows, 'season'))),
            ]);

            foreach ($groupKeys as $i => $k) {
                $row[$k] = $parts[$i] ?? null;
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * Spread statistics (mean/median/std/min/max) for a list of per-season values.
     *
     * @param list<float|null> $values
     * @return array<string,mixed>
     */
    protected function spread(array $values): array
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));

        if (empty($values)) {
            return ['mean' => null, 'median' => null, 'std' => null, 'min' => null, 'max' => null];
        }

        $n = count($values);
        $mean = array_sum($values) / $n;

        sort($values);

        $median = $n % 2 === 0
            ? ($values[$n / 2 - 1] + $values[$n / 2]) / 2
            : $values[($n - 1) / 2];

        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / $n;

        return [
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'std' => round(sqrt($variance), 2),
            'min' => round(min($values), 2),
            'max' => round(max($values), 2),
        ];
    }
}
