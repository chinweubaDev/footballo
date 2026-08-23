<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\League;
use App\Models\Prediction;
use App\Services\Prediction\Support\PredictionCacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Computes live-prediction performance analytics for the admin dashboard.
 *
 * The service MEASURES accuracy; it never tunes the model. All outputs carry
 * sample sizes so small samples are never presented as meaningful, and the
 * minimum-sample-size threshold is respected when ranking.
 *
 * Results are cached; caches are invalidated when new results are resolved,
 * when a backtest completes, or when the model version changes.
 */
class PerformanceAnalyticsService
{
    public function __construct(protected MetricsCalculator $calculator)
    {
    }

    /**
     * The full dashboard payload (all sections in one cached computation).
     *
     * @return array<string,mixed>
     */
    public function dashboard(): array
    {
        $key = PredictionCacheKeys::performanceDashboard();

        return Cache::remember($key, (int) config('evaluation.cache_ttl', 300), function () {
            $rows = $this->loadResolvedRows();

            return [
                'overview' => $this->overview($rows),
                'leagues' => $this->leaguePerformance($rows),
                'markets' => $this->marketPerformance($rows),
                'league_market_matrix' => $this->leagueMarketMatrix($rows),
                'confidence' => $this->confidencePerformance($rows),
                'calibration' => $this->probabilityCalibration($rows),
                'model_versions' => $this->modelVersionPerformance($rows),
                'overrides' => $this->overridePerformance(),
                'no_bet' => $this->noBetAnalysis($rows),
                'data_quality' => $this->dataQualityPerformance($rows),
                'selectivity' => $this->calculator->selectivity($this->bettable($rows)),
                'ranking' => $this->marketRanking($rows),
                'over_time' => $this->performanceOverTime(),
                'minimum_sample_size' => (int) config('evaluation.minimum_sample_size', 100),
            ];
        });
    }

    /**
     * Headline overview numbers.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    public function overview(array $rows): array
    {
        $bettable = $this->bettable($rows);
        $summary = $this->calculator->summarize($bettable);

        $totalFixtures = count(array_unique(array_map(fn ($r) => $r['fixture_id'] ?? null, $rows)));
        $predictedFixtures = count(array_unique(array_map(fn ($r) => $r['fixture_id'] ?? null, $bettable)));

        return array_merge($summary, [
            'coverage_percent' => $totalFixtures > 0 ? round($predictedFixtures / $totalFixtures * 100, 2) : null,
            'total_fixtures' => $totalFixtures,
            'predicted_fixtures' => $predictedFixtures,
        ]);
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function leaguePerformance(array $rows): array
    {
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $groups = $this->groupRows($rows, 'league_id');

        $result = [];

        foreach ($groups as $leagueId => $groupRows) {
            $bettable = $this->bettable($groupRows);
            $summary = $this->calculator->summarize($bettable);

            $result[] = array_merge($summary, [
                'league_id' => $leagueId,
                'league_name' => $names[$leagueId] ?? "League {$leagueId}",
                'insufficient' => $summary['resolved'] < (int) config('evaluation.minimum_sample_size', 100),
            ]);
        }

        usort($result, fn ($a, $b) => ($b['resolved'] ?? 0) <=> ($a['resolved'] ?? 0));

        return $result;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function marketPerformance(array $rows): array
    {
        $groups = $this->groupRows($rows, 'market_code');
        $result = [];

        foreach ($groups as $market => $groupRows) {
            $bettable = $this->bettable($groupRows);
            $summary = $this->calculator->summarize($bettable);

            $result[] = array_merge($summary, [
                'market_code' => $market,
                'insufficient' => $summary['resolved'] < (int) config('evaluation.minimum_sample_size', 100),
            ]);
        }

        usort($result, fn ($a, $b) => ($b['resolved'] ?? 0) <=> ($a['resolved'] ?? 0));

        return $result;
    }

    /**
     * League × market matrix of accuracy with sample sizes.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    public function leagueMarketMatrix(array $rows): array
    {
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $matrix = [];

        foreach ($rows as $row) {
            $league = $row['league_id'] ?? null;
            $market = $row['market_code'] ?? 'unknown';

            if ($league === null) {
                continue;
            }

            $matrix[$league][$market][] = $row;
        }

        $result = [];

        foreach ($matrix as $leagueId => $markets) {
            $leagueResult = [
                'league_id' => $leagueId,
                'league_name' => $names[$leagueId] ?? "League {$leagueId}",
                'markets' => [],
            ];

            foreach ($markets as $market => $marketRows) {
                $bettable = $this->bettable($marketRows);
                $summary = $this->calculator->summarize($bettable);

                $leagueResult['markets'][$market] = array_merge($summary, [
                    'market_code' => $market,
                    'insufficient' => $summary['resolved'] < (int) config('evaluation.minimum_sample_size', 100),
                ]);
            }

            $result[] = $leagueResult;
        }

        return $result;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function confidencePerformance(array $rows): array
    {
        return $this->calculator->confidenceBuckets($this->bettable($rows));
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function probabilityCalibration(array $rows): array
    {
        return $this->calculator->probabilityBuckets($this->bettable($rows));
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function modelVersionPerformance(array $rows): array
    {
        $groups = $this->groupRows($rows, 'model_version');
        $result = [];

        foreach ($groups as $version => $groupRows) {
            $bettable = $this->bettable($groupRows);
            $summary = $this->calculator->summarize($bettable);

            $result[] = array_merge($summary, [
                'model_version' => $version,
                'insufficient' => $summary['resolved'] < (int) config('evaluation.minimum_sample_size', 100),
            ]);
        }

        usort($result, fn ($a, $b) => strcmp($b['model_version'] ?? '', $a['model_version'] ?? ''));

        return $result;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public function dataQualityPerformance(array $rows): array
    {
        $bettable = $this->bettable($rows);
        $buckets = [
            '90-100' => fn ($q) => $q >= 90,
            '80-89' => fn ($q) => $q >= 80 && $q < 90,
            '70-79' => fn ($q) => $q >= 70 && $q < 80,
            '<70' => fn ($q) => $q !== null && $q < 70,
            'unknown' => fn ($q) => $q === null,
        ];

        $result = [];

        foreach ($buckets as $label => $predicate) {
            $filtered = array_values(array_filter($bettable, fn ($r) => $predicate($r['data_quality_score'] ?? null)));
            $summary = $this->calculator->summarize($filtered);

            $result[] = array_merge($summary, ['label' => $label]);
        }

        return $result;
    }

    /**
     * Original model vs admin override comparison (resolved predictions only).
     *
     * @return array<string,mixed>
     */
    public function overridePerformance(): array
    {
        $overridden = Prediction::query()
            ->whereNotNull('result')
            ->whereNotNull('admin_selection')
            ->select(['result', 'model_result', 'override_result'])
            ->get();

        $modelWon = $modelLost = 0;
        $overrideWon = $overrideLost = 0;

        foreach ($overridden as $p) {
            if (in_array($p->model_result, ['won', 'lost'], true)) {
                $p->model_result === 'won' ? $modelWon++ : $modelLost++;
            }

            if (in_array($p->override_result, ['won', 'lost'], true)) {
                $p->override_result === 'won' ? $overrideWon++ : $overrideLost++;
            }
        }

        return [
            'model' => [
                'total' => $modelWon + $modelLost,
                'won' => $modelWon,
                'lost' => $modelLost,
                'accuracy' => $this->calculator->accuracy($modelWon, $modelLost),
            ],
            'override' => [
                'total' => $overrideWon + $overrideLost,
                'won' => $overrideWon,
                'lost' => $overrideLost,
                'accuracy' => $this->calculator->accuracy($overrideWon, $overrideLost),
            ],
            'overridden_count' => $overridden->count(),
        ];
    }

    /**
     * NO_BET analysis: how many predictions the model declined, and what the
     * would-be result of those declined selections was.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    public function noBetAnalysis(array $rows): array
    {
        $noBet = array_values(array_filter($rows, fn ($r) => ($r['status'] ?? null) === 'no_bet'));

        // Distinct fixtures with any prediction vs fixtures with any bettable prediction.
        $allFixtures = array_unique(array_map(fn ($r) => $r['fixture_id'] ?? null, array_filter($rows, fn ($r) => ($r['fixture_id'] ?? null) !== null)));
        $bettableFixtures = array_unique(array_map(fn ($r) => $r['fixture_id'] ?? null, $this->bettable($rows)));

        return [
            'count' => count($noBet),
            'total_fixtures' => count($allFixtures),
            'predicted_fixtures' => count($bettableFixtures),
            'coverage_percent' => count($allFixtures) > 0 ? round(count($bettableFixtures) / count($allFixtures) * 100, 2) : null,
            'would_be' => $this->calculator->summarize($noBet),
        ];
    }

    /**
     * Market ranking by actual accuracy (minimum sample size enforced).
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    public function marketRanking(array $rows): array
    {
        $minSample = (int) config('evaluation.minimum_sample_size', 100);
        $markets = $this->marketPerformance($rows);

        $ranked = array_values(array_filter($markets, fn ($m) => ! $m['insufficient']));
        $insufficient = array_values(array_filter($markets, fn ($m) => $m['insufficient']));

        usort($ranked, fn ($a, $b) => ($b['accuracy'] ?? 0) <=> ($a['accuracy'] ?? 0));

        return [
            'ranked' => array_map(fn ($m) => [
                'market_code' => $m['market_code'],
                'accuracy' => $m['accuracy'],
                'resolved' => $m['resolved'],
            ], $ranked),
            'insufficient' => array_map(fn ($m) => [
                'market_code' => $m['market_code'],
                'accuracy' => $m['accuracy'],
                'resolved' => $m['resolved'],
            ], $insufficient),
            'minimum_sample_size' => $minSample,
        ];
    }

    /**
     * Performance over time by month (model drift detection).
     *
     * @return list<array<string,mixed>>
     */
    public function performanceOverTime(): array
    {
        $rows = Prediction::query()
            ->whereNotNull('result')
            ->whereIn('result', ['won', 'lost'])
            ->select(['result', 'resolved_at'])
            ->get();

        $byMonth = [];

        foreach ($rows as $p) {
            if (! $p->resolved_at) {
                continue;
            }

            $key = $p->resolved_at->format('Y-m');
            $byMonth[$key]['won'] = ($byMonth[$key]['won'] ?? 0) + ($p->result === 'won' ? 1 : 0);
            $byMonth[$key]['lost'] = ($byMonth[$key]['lost'] ?? 0) + ($p->result === 'lost' ? 1 : 0);
        }

        ksort($byMonth);

        $result = [];

        foreach ($byMonth as $month => $counts) {
            $result[] = [
                'month' => $month,
                'won' => $counts['won'],
                'lost' => $counts['lost'],
                'total' => $counts['won'] + $counts['lost'],
                'accuracy' => $this->calculator->accuracy($counts['won'], $counts['lost']),
            ];
        }

        return $result;
    }

    /**
     * Invalidate every performance cache entry.
     */
    public function flush(): void
    {
        Cache::forget(PredictionCacheKeys::performanceDashboard());
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    protected function bettable(array $rows): array
    {
        return array_values(array_filter($rows, fn ($r) => ($r['status'] ?? null) !== 'no_bet'));
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array<string,list<array<string,mixed>>>
     */
    protected function groupRows(array $rows, string $key): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $value = $value === null || $value === '' ? 'unknown' : (string) $value;
            $groups[$value][] = $row;
        }

        return $groups;
    }

    /**
     * Load all resolved prediction rows (small column set) for aggregation.
     *
     * @return list<array<string,mixed>>
     */
    protected function loadResolvedRows(): array
    {
        $rows = [];

        Prediction::query()
            ->whereNotNull('result')
            ->where(fn ($q) => $q->whereNull('provenance_status')->orWhere('provenance_status', '!=', 'invalid'))
            ->select([
                'id', 'fixture_id', 'market_code', 'probability', 'confidence',
                'model_version', 'league_id', 'data_quality_score', 'result', 'status',
            ])
            ->chunkById(500, function ($predictions) use (&$rows) {
                foreach ($predictions as $p) {
                    $rows[] = [
                        'fixture_id' => $p->fixture_id,
                        'market_code' => $p->market_code ?? 'unknown',
                        'probability' => (float) ($p->probability ?? 0),
                        'confidence' => (int) ($p->confidence ?? 0),
                        'model_version' => $p->model_version ?? 'unknown',
                        'league_id' => $p->league_id,
                        'data_quality_score' => $p->data_quality_score !== null ? (int) $p->data_quality_score : null,
                        'result' => $p->result,
                        'status' => $p->status,
                    ];
                }
            });

        return $rows;
    }
}
