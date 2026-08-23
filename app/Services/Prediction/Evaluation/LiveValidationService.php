<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionModel;

/**
 * Phase 1M — live validation, model comparison & evidence analysis.
 *
 * Operates ONLY on resolved live predictions (result != null). It MEASURES
 * evidence; it never tunes the model, never changes gates, never fabricates
 * results, and never promotes a shadow model. Every output carries a sample
 * size so small samples are never presented as conclusive.
 */
class LiveValidationService
{
    public const INSUFFICIENT = 'INSUFFICIENT';
    public const PRELIMINARY = 'PRELIMINARY';
    public const MEANINGFUL = 'MEANINGFUL';
    public const STRONGER = 'STRONGER EVIDENCE';

    public function __construct(protected MetricsCalculator $calculator)
    {
    }

    /**
     * @return list<string> model versions that exist in the system.
     */
    public function versions(): array
    {
        return PredictionModel::query()->orderBy('version')->pluck('version')->all();
    }

    /**
     * Evidence-strength label for a resolved sample size.
     */
    public function evidenceLabel(int $n): string
    {
        $preliminary = (int) config('evaluation.evidence.preliminary', 50);
        $meaningful = (int) config('evaluation.evidence.meaningful', 100);
        $strong = (int) config('evaluation.evidence.strong', 500);

        if ($n >= $strong) {
            return self::STRONGER;
        }

        if ($n >= $meaningful) {
            return self::MEANINGFUL;
        }

        if ($n >= $preliminary) {
            return self::PRELIMINARY;
        }

        return self::INSUFFICIENT;
    }

    /**
     * Headline per-model live summary (resolved only).
     *
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        $rows = $this->resolvedRows();
        $versions = $this->versions();
        $models = [];
        $totalResolved = 0;

        foreach ($versions as $version) {
            $versionRows = array_values(array_filter($rows, fn ($r) => $r['model_version'] === $version));
            $summary = $this->calculator->summarize($versionRows);

            $models[$version] = array_merge($summary, [
                'model_version' => $version,
                'evidence' => $this->evidenceLabel($summary['resolved']),
                'active' => PredictionModel::query()->where('version', $version)->value('active') ?? false,
            ]);

            $totalResolved += $summary['resolved'];
        }

        return [
            'total_resolved' => $totalResolved,
            'evidence' => $this->evidenceLabel($totalResolved),
            'models' => $models,
            'counters' => $this->counters(),
        ];
    }

    /**
     * Prediction-level counters (total, locked, in-progress, settled,
     * pending-review, provenance-invalid/uncertain, published, no-bet) split
     * by model version. Powers the live-evidence monitor.
     *
     * @return array<string,mixed>
     */
    public function counters(): array
    {
        $models = [];

        foreach ($this->versions() as $version) {
            $base = Prediction::query()->where('model_version', $version);

            $total = (clone $base)->count();
            $locked = (clone $base)->whereNotNull('locked_at')->count();
            $settled = (clone $base)->whereNotNull('settled_at')->count();
            $pendingReview = (clone $base)->where('settlement_status', 'pending_review')->count();
            $provenanceInvalid = (clone $base)->where('provenance_status', 'invalid')->count();
            $provenanceUncertain = (clone $base)->where('provenance_status', 'provenance_uncertain')->count();
            $inProgress = (clone $base)->whereNotNull('locked_at')->whereNull('settled_at')->count();
            $published = (clone $base)->where('status', 'published')->count();
            $noBet = (clone $base)->where('status', 'no_bet')->count();

            $models[$version] = [
                'model_version' => $version,
                'predictions' => $total,
                'locked' => $locked,
                'in_progress' => $inProgress,
                'settled' => $settled,
                'pending_review' => $pendingReview,
                'provenance_invalid' => $provenanceInvalid,
                'provenance_uncertain' => $provenanceUncertain,
                'published' => $published,
                'no_bet' => $noBet,
                'coverage' => $total > 0 ? round($published / $total * 100, 2) : null,
            ];
        }

        return [
            'total_predictions' => array_sum(array_column($models, 'predictions')),
            'total_locked' => array_sum(array_column($models, 'locked')),
            'total_settled' => array_sum(array_column($models, 'settled')),
            'total_pending_review' => array_sum(array_column($models, 'pending_review')),
            'total_provenance_invalid' => array_sum(array_column($models, 'provenance_invalid')),
            'total_provenance_uncertain' => array_sum(array_column($models, 'provenance_uncertain')),
            'models' => $models,
        ];
    }

    /**
     * Paired comparison between two model versions on identical resolved
     * fixtures. Excludes void/provenance-invalid observations.
     *
     * @return array<string,mixed>
     */
    public function pairedComparison(string $versionA = 'v1.0.0', string $versionB = 'v1.1.0'): array
    {
        $rows = $this->resolvedRows();

        $a = [];
        $b = [];

        foreach ($rows as $row) {
            if ($row['model_version'] === $versionA) {
                $a[$row['fixture_id'].'|'.$row['market_code']] = $row;
            }

            if ($row['model_version'] === $versionB) {
                $b[$row['fixture_id'].'|'.$row['market_code']] = $row;
            }
        }

        $paired = [];
        $keys = array_intersect(array_keys($a), array_keys($b));

        foreach ($keys as $key) {
            if (! in_array($a[$key]['result'] ?? null, ['won', 'lost'], true)
                || ! in_array($b[$key]['result'] ?? null, ['won', 'lost'], true)) {
                continue;
            }

            $paired[] = ['a' => $a[$key], 'b' => $b[$key]];
        }

        $bothWon = $aOnly = $bOnly = $bothLost = 0;

        foreach ($paired as $pair) {
            $aw = $pair['a']['result'] === 'won';
            $bw = $pair['b']['result'] === 'won';

            if ($aw && $bw) {
                $bothWon++;
            } elseif ($aw && ! $bw) {
                $aOnly++;
            } elseif (! $aw && $bw) {
                $bOnly++;
            } else {
                $bothLost++;
            }
        }

        $aRows = array_column($paired, 'a');
        $bRows = array_column($paired, 'b');

        $sumA = $this->calculator->summarize($aRows);
        $sumB = $this->calculator->summarize($bRows);

        return [
            'version_a' => $versionA,
            'version_b' => $versionB,
            'paired' => count($paired),
            'win_matrix' => [
                'both_won' => $bothWon,
                'a_won_b_lost' => $aOnly,
                'a_lost_b_won' => $bOnly,
                'both_lost' => $bothLost,
            ],
            'a' => array_merge($sumA, ['evidence' => $this->evidenceLabel($sumA['resolved'])]),
            'b' => array_merge($sumB, ['evidence' => $this->evidenceLabel($sumB['resolved'])]),
            'diffs' => [
                'accuracy' => $this->difference($sumA['accuracy'], $sumB['accuracy']),
                'brier_score' => $this->difference($sumB['brier_score'], $sumA['brier_score']), // negative = B better
                'log_loss' => $this->difference($sumB['log_loss'], $sumA['log_loss']),
                'calibration_error' => $this->difference($sumB['calibration_error'], $sumA['calibration_error']),
            ],
            'mcnemar' => $this->mcnemar($aOnly, $bOnly),
        ];
    }

    /**
     * Model agreement per market: do the two models pick the same selection?
     *
     * @return array<string,mixed>
     */
    public function modelAgreement(string $versionA = 'v1.0.0', string $versionB = 'v1.1.0'): array
    {
        $rows = $this->resolvedRows();

        $a = [];
        $b = [];

        foreach ($rows as $row) {
            if ($row['model_version'] === $versionA) {
                $a[$row['fixture_id'].'|'.$row['market_code']] = $row;
            }

            if ($row['model_version'] === $versionB) {
                $b[$row['fixture_id'].'|'.$row['market_code']] = $row;
            }
        }

        $markets = [];

        foreach (array_intersect(array_keys($a), array_keys($b)) as $key) {
            $market = $a[$key]['market_code'] ?? 'unknown';
            $markets[$market]['pairs'] = ($markets[$market]['pairs'] ?? 0) + 1;
            $same = ($a[$key]['selection'] ?? null) === ($b[$key]['selection'] ?? null);
            $markets[$market]['same'] = ($markets[$market]['same'] ?? 0) + ($same ? 1 : 0);
        }

        $out = [];
        $totalPairs = 0;
        $totalSame = 0;

        foreach ($markets as $market => $stats) {
            $pairs = $stats['pairs'];
            $same = $stats['same'];
            $totalPairs += $pairs;
            $totalSame += $same;

            $out[] = [
                'market_code' => $market,
                'pairs' => $pairs,
                'same_selection' => $same,
                'different_selection' => $pairs - $same,
                'agreement_percent' => $pairs > 0 ? round($same / $pairs * 100, 2) : null,
            ];
        }

        usort($out, fn ($x, $y) => ($y['pairs'] ?? 0) <=> ($x['pairs'] ?? 0));

        return [
            'version_a' => $versionA,
            'version_b' => $versionB,
            'markets' => $out,
            'total_pairs' => $totalPairs,
            'total_same' => $totalSame,
            'overall_agreement_percent' => $totalPairs > 0 ? round($totalSame / $totalPairs * 100, 2) : null,
        ];
    }

    /**
     * Analytical gate sweep (60/60 … 80/80) over resolved live predictions,
     * per model version. Never applied to production settings.
     *
     * @return list<array<string,mixed>>
     */
    public function gateAnalysis(): array
    {
        $rows = $this->resolvedRows();
        $pairs = config('evaluation.matrix.gate_comparison', [[60, 60], [65, 65], [70, 70], [70, 75], [75, 75], [80, 80]]);
        $versions = $this->versions();
        $out = [];

        foreach ($pairs as [$minProbability, $minConfidence]) {
            $row = [
                'label' => "{$minProbability}/{$minConfidence}",
                'min_probability' => (int) $minProbability,
                'min_confidence' => (int) $minConfidence,
                'models' => [],
            ];

            foreach ($versions as $version) {
                $versionRows = array_values(array_filter($rows, fn ($r) => $r['model_version'] === $version));
                $filtered = array_values(array_filter(
                    $versionRows,
                    fn ($r) => ($r['probability'] ?? 0) >= $minProbability && ($r['confidence'] ?? 0) >= $minConfidence
                ));
                $summary = $this->calculator->summarize($filtered);

                $row['models'][$version] = array_merge($summary, [
                    'coverage' => count($versionRows) > 0 ? round(count($filtered) / count($versionRows) * 100, 2) : null,
                ]);
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * Sure Picks evaluation (1X2 only, resolved, active production model).
     *
     * @return array<string,mixed>
     */
    public function surePicksEvaluation(): array
    {
        $active = PredictionModel::query()->where('active', true)->value('version');

        return $this->specialEvaluation('surepick', ['1x2'], $active, true);
    }

    /**
     * Most Featured evaluation (1X2 + Double Chance, resolved).
     *
     * @return array<string,mixed>
     */
    public function mostFeaturedEvaluation(): array
    {
        return $this->specialEvaluation('featured', ['1x2', 'double_chance'], null, false);
    }

    /**
     * @param list<string> $markets
     * @return array<string,mixed>
     */
    protected function specialEvaluation(string $kind, array $markets, ?string $version, bool $publishedOnly): array
    {
        $query = Prediction::query()
            ->whereNotNull('result')
            ->whereIn('market_code', $markets);

        if ($kind === 'surepick') {
            $query->whereNotNull('surepick_tip_content');
        }

        if ($kind === 'featured') {
            $query->where(fn ($q) => $q->where('featured', true)->orWhere('admin_featured', true));
        }

        if ($version !== null) {
            $query->where('model_version', $version);
        }

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        $resolved = $query->get();

        $generated = Prediction::query()
            ->whereIn('market_code', $markets);

        if ($kind === 'surepick') {
            $generated->whereNotNull('surepick_tip_content');
        }

        if ($kind === 'featured') {
            $generated->where(fn ($q) => $q->where('featured', true)->orWhere('admin_featured', true));
        }

        if ($version !== null) {
            $generated->where('model_version', $version);
        }

        if ($publishedOnly) {
            $generated->where('status', 'published');
        }

        $generatedCount = (clone $generated)->count();
        $publishedCount = (clone $generated)->where('status', 'published')->count();

        $won = $resolved->where('result', 'won')->count();
        $lost = $resolved->where('result', 'lost')->count();
        $void = $resolved->where('result', 'void')->count();

        $rows = $resolved->whereIn('result', ['won', 'lost'])->map(fn ($p) => [
            'result' => $p->result,
            'probability' => (float) ($p->probability ?? 0),
            'confidence' => (int) ($p->confidence ?? 0),
        ])->all();

        $summary = $this->calculator->summarize($rows);

        return [
            'kind' => $kind,
            'markets' => $markets,
            'model_version' => $version,
            'generated' => $generatedCount,
            'published' => $publishedCount,
            'resolved' => $summary['resolved'],
            'wins' => $summary['won'],
            'losses' => $summary['lost'],
            'voids' => $void,
            'accuracy' => $summary['accuracy'],
            'brier_score' => $summary['brier_score'],
            'evidence' => $this->evidenceLabel($summary['resolved']),
        ];
    }

    /**
     * Market performance split by model version.
     *
     * @return array<string,list<array<string,mixed>>>
     */
    public function marketPerformanceByModel(): array
    {
        $rows = $this->resolvedRows();
        $result = [];

        foreach ($this->versions() as $version) {
            $versionRows = array_values(array_filter($rows, fn ($r) => $r['model_version'] === $version));
            $groups = $this->group($versionRows, 'market_code');
            $markets = [];

            foreach ($groups as $market => $groupRows) {
                $summary = $this->calculator->summarize($groupRows);
                $markets[] = array_merge($summary, [
                    'market_code' => $market,
                    'evidence' => $this->evidenceLabel($summary['resolved']),
                ]);
            }

            usort($markets, fn ($x, $y) => ($y['resolved'] ?? 0) <=> ($x['resolved'] ?? 0));
            $result[$version] = $markets;
        }

        return $result;
    }

    /**
     * League performance split by model version.
     *
     * @return array<string,list<array<string,mixed>>>
     */
    public function leaguePerformanceByModel(): array
    {
        $rows = $this->resolvedRows();
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $result = [];

        foreach ($this->versions() as $version) {
            $versionRows = array_values(array_filter($rows, fn ($r) => $r['model_version'] === $version));
            $groups = $this->group($versionRows, 'league_id');
            $leagues = [];

            foreach ($groups as $leagueId => $groupRows) {
                $summary = $this->calculator->summarize($groupRows);
                $leagues[] = array_merge($summary, [
                    'league_id' => $leagueId,
                    'league_name' => $names[$leagueId] ?? "League {$leagueId}",
                    'evidence' => $this->evidenceLabel($summary['resolved']),
                ]);
            }

            usort($leagues, fn ($x, $y) => ($y['resolved'] ?? 0) <=> ($x['resolved'] ?? 0));
            $result[$version] = $leagues;
        }

        return $result;
    }

    /**
     * League × Market matrix split by model version.
     *
     * @return array<string,list<array<string,mixed>>>
     */
    public function leagueMarketMatrixByModel(): array
    {
        $rows = $this->resolvedRows();
        $names = League::query()->pluck('name', 'api_football_league_id')->all();
        $result = [];

        foreach ($this->versions() as $version) {
            $versionRows = array_values(array_filter($rows, fn ($r) => $r['model_version'] === $version));
            $matrix = [];

            foreach ($versionRows as $row) {
                $league = $row['league_id'] ?? null;

                if ($league === null) {
                    continue;
                }

                $matrix[$league][$row['market_code'] ?? 'unknown'][] = $row;
            }

            $leagues = [];

            foreach ($matrix as $leagueId => $markets) {
                $entry = [
                    'league_id' => $leagueId,
                    'league_name' => $names[$leagueId] ?? "League {$leagueId}",
                    'markets' => [],
                ];

                foreach ($markets as $market => $marketRows) {
                    $summary = $this->calculator->summarize($marketRows);
                    $entry['markets'][$market] = array_merge($summary, [
                        'market_code' => $market,
                        'evidence' => $this->evidenceLabel($summary['resolved']),
                    ]);
                }

                $leagues[] = $entry;
            }

            usort($leagues, fn ($x, $y) => ($x['league_id'] ?? 0) <=> ($y['league_id'] ?? 0));
            $result[$version] = $leagues;
        }

        return $result;
    }

    /**
     * The audit dataset for the live-validation export. Returns Eloquent
     * models (resolved only) so the controller can stream a CSV.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,Prediction>
     */
    public function exportModels()
    {
        return Prediction::query()
            ->whereNotNull('result')
            ->with(['fixture', 'league'])
            ->orderBy('fixture_id')
            ->get();
    }

    /**
     * Resolved live rows (provenance-invalid excluded), keyed for aggregation.
     *
     * @return list<array<string,mixed>>
     */
    protected function resolvedRows(): array
    {
        $rows = [];

        Prediction::query()
            ->whereNotNull('result')
            ->where(fn ($q) => $q->whereNull('provenance_status')->orWhere('provenance_status', '!=', 'invalid'))
            ->select([
                'id', 'fixture_id', 'market_code', 'selection', 'probability', 'confidence',
                'model_version', 'league_id', 'result',
            ])
            ->chunkById(500, function ($predictions) use (&$rows) {
                foreach ($predictions as $p) {
                    $rows[] = [
                        'fixture_id' => $p->fixture_id,
                        'market_code' => $p->market_code ?? 'unknown',
                        'selection' => $p->selection,
                        'probability' => (float) ($p->probability ?? 0),
                        'confidence' => (int) ($p->confidence ?? 0),
                        'model_version' => $p->model_version ?? 'unknown',
                        'league_id' => $p->league_id,
                        'result' => $p->result,
                    ];
                }
            });

        return $rows;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array<string,list<array<string,mixed>>>
     */
    protected function group(array $rows, string $key): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $value = $value === null || $value === '' ? 'unknown' : (string) $value;
            $groups[$value][] = $row;
        }

        return $groups;
    }

    protected function difference(?float $a, ?float $b): ?float
    {
        if ($a === null || $b === null) {
            return null;
        }

        return round($a - $b, 4);
    }

    /**
     * Exact McNemar (two-sided binomial) test for paired classification.
     *
     * @return array<string,mixed>
     */
    protected function mcnemar(int $b, int $c): array
    {
        $n = $b + $c;

        if ($n === 0) {
            return ['discordant' => 0, 'b' => 0, 'c' => 0, 'p_value' => null, 'significant' => false, 'note' => 'no_discordant_pairs'];
        }

        $p = $this->binomialTwoSided(min($b, $c), $n, 0.5);

        return [
            'discordant' => $n,
            'b' => $b,
            'c' => $c,
            'p_value' => round($p, 4),
            'significant' => $p < (float) config('evaluation.significance.alpha', 0.05),
            'note' => 'two-sided exact binomial',
        ];
    }

    protected function binomialTwoSided(int $k, int $n, float $p): float
    {
        $target = $this->binomialPmf($k, $n, $p);
        $sum = 0.0;

        for ($i = 0; $i <= $n; $i++) {
            $pmf = $this->binomialPmf($i, $n, $p);

            if ($pmf <= $target + 1e-15) {
                $sum += $pmf;
            }
        }

        return min(1.0, $sum);
    }

    protected function binomialPmf(int $k, int $n, float $p): float
    {
        if ($p == 0.0) {
            return $k === 0 ? 1.0 : 0.0;
        }

        if ($p == 1.0) {
            return $k === $n ? 1.0 : 0.0;
        }

        return exp($this->logCombinations($n, $k) + $k * log($p) + ($n - $k) * log(1.0 - $p));
    }

    protected function logCombinations(int $n, int $k): float
    {
        if ($k < 0 || $k > $n) {
            return -INF;
        }

        $k = min($k, $n - $k);
        $result = 0.0;

        for ($i = 1; $i <= $k; $i++) {
            $result += log($n - $k + $i) - log($i);
        }

        return $result;
    }
}
