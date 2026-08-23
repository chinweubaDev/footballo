<?php

namespace App\Services\Prediction\Validation;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\League;
use App\Models\PredictionModel;
use App\Models\PublicationCandidate;

/**
 * Phase 1G.2 — full League x Market x Model validation matrix.
 *
 * Computes everything from resolved backtest predictions. The canonical
 * dataset is the LATEST completed run per (league, season, model_version),
 * which avoids double-counting repeated validation runs for the same league.
 *
 * No value is fabricated: a model version with no completed backtest runs is
 * reported as NOT AVAILABLE, and every figure carries its sample size.
 */
class ValidationMatrixService
{
    public const MARKETS = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];

    public const MARKET_LABELS = [
        '1x2' => '1X2',
        'draw' => 'Draw',
        'double_chance' => 'Double Chance',
        'over_1_5' => 'Over 1.5',
        'over_2_5' => 'Over 2.5',
        'btts' => 'BTTS',
        'correct_score' => 'Correct Score',
    ];

    /**
     * Selection breakdowns requested by the report.
     */
    protected const SELECTION_GROUPS = [
        '1x2' => ['home', 'draw', 'away'],
        'double_chance' => ['1x', 'x2', '12'],
    ];

    public function label(string $code): string
    {
        return self::MARKET_LABELS[$code] ?? $code;
    }

    /**
     * @return list<BacktestRun>
     */
    public function canonicalRuns(?int $season = null, ?string $modelVersion = null): array
    {
        $query = BacktestRun::query()
            ->where('status', BacktestRun::STATUS_COMPLETED)
            ->when($season !== null, fn ($q) => $q->where('season', $season))
            ->when($modelVersion !== null, fn ($q) => $q->where('model_version', $modelVersion))
            ->orderBy('id');

        $latest = [];

        foreach ($query->get() as $run) {
            $key = implode('|', [$run->league_id ?? 'all', $run->season ?? 'any', $run->model_version ?? 'none']);
            // Later runs (higher id) replace earlier ones for the same key.
            $latest[$key] = $run;
        }

        return array_values($latest);
    }

    /**
     * Load all resolved rows for the given runs, tagged with their league.
     *
     * @param list<BacktestRun> $runs
     * @return list<array<string,mixed>>
     */
    public function loadRows(array $runs): array
    {
        if (empty($runs)) {
            return [];
        }

        $runIds = array_map(fn ($r) => $r->id, $runs);
        $leagueByRun = [];

        foreach ($runs as $run) {
            $leagueByRun[$run->id] = [
                'league_id' => $run->league_id,
                'season' => $run->season,
                'model_version' => $run->model_version,
                'total_fixtures' => $run->total_fixtures,
            ];
        }

        $rows = BacktestPrediction::query()
            ->whereIn('backtest_run_id', $runIds)
            ->whereIn('result', ['won', 'lost'])
            ->get(['backtest_run_id', 'market_code', 'selection', 'probability', 'confidence', 'model_version', 'result', 'status']);

        $out = [];

        foreach ($rows as $p) {
            $meta = $leagueByRun[$p->backtest_run_id] ?? null;

            if ($meta === null) {
                continue;
            }

            $out[] = [
                'league_id' => $meta['league_id'],
                'season' => $meta['season'],
                'model_version' => $meta['model_version'],
                'total_fixtures' => $meta['total_fixtures'],
                'market_code' => $p->market_code,
                'selection' => $p->selection,
                'probability' => (float) $p->probability,
                'confidence' => (int) $p->confidence,
                'result' => $p->result,
            ];
        }

        return $out;
    }

    /**
     * Build the complete matrix.
     *
     * @return array<string,mixed>
     */
    public function matrix(?int $season = null, ?string $modelVersion = null): array
    {
        $versions = $this->versions($modelVersion);
        $leagueNames = League::query()->pluck('name', 'api_football_league_id')->all();

        $perModel = [];
        $allLeagues = [];

        foreach ($versions as $version) {
            $runs = $this->canonicalRuns($season, $version);
            $rows = $this->loadRows($runs);
            $leagues = $this->leagueMatrix($rows, $leagueNames);

            $perModel[$version] = [
                'available' => ! empty($runs),
                'runs' => count($runs),
                'leagues' => $leagues,
                'market_summary' => $this->marketSummary($leagues),
                'league_summary' => $this->leagueSummary($leagues),
                'ranking' => $this->ranking($leagues, $version),
            ];

            foreach ($leagues as $league) {
                $allLeagues[] = $league;
            }
        }

        // Merge candidates and market/league classifications across models.
        $perModel = $this->attachCandidates($perModel, $leagueNames);

        return [
            'season' => $season,
            'versions' => $versions,
            'models' => $perModel,
            'market_labels' => self::MARKET_LABELS,
            'model_comparison' => $this->modelComparison($versions, $perModel),
            'strong_markets' => $this->strongWeakMarkets($versions, $perModel, 'strong'),
            'weak_markets' => $this->strongWeakMarkets($versions, $perModel, 'weak'),
            'minimum_sample_size' => (int) config('evaluation.minimum_sample_size', 100),
            'thresholds' => config('evaluation.matrix'),
        ];
    }

    /**
     * @return list<string>
     */
    protected function versions(?string $modelVersion): array
    {
        if ($modelVersion !== null) {
            return [$modelVersion];
        }

        return PredictionModel::query()->orderBy('version')->pluck('version')->all();
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @param array<int,string> $leagueNames
     * @return list<array<string,mixed>>
     */
    protected function leagueMatrix(array $rows, array $leagueNames): array
    {
        // Group rows by league, then market.
        $grouped = [];

        foreach ($rows as $r) {
            $grouped[$r['league_id']][$r['market_code']][] = $r;
        }

        $out = [];

        foreach ($grouped as $leagueId => $markets) {
            $league = [
                'league_id' => $leagueId,
                'league_name' => $leagueNames[$leagueId] ?? "League {$leagueId}",
                'season' => null,
                'fixtures' => null,
                'markets' => [],
            ];

            $marketRows = [];

            foreach (self::MARKETS as $code) {
                $rowsForMarket = $markets[$code] ?? [];

                if (empty($rowsForMarket)) {
                    continue;
                }

                $league['season'] = $league['season'] ?? $rowsForMarket[0]['season'];
                $league['fixtures'] = $league['fixtures'] ?? $rowsForMarket[0]['total_fixtures'];

                $marketRows[$code] = $this->marketStats($rowsForMarket);
            }

            $league['markets'] = $marketRows;
            $out[] = $league;
        }

        // Stable ordering by league id.
        usort($out, fn ($a, $b) => ($a['league_id'] ?? 0) <=> ($b['league_id'] ?? 0));

        return $out;
    }

    /**
     * Full statistics for one league x market cell.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    protected function marketStats(array $rows): array
    {
        $resolved = array_values(array_filter($rows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
        $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
        $n = count($resolved);
        $losses = $n - $won;

        $totalFixtures = max(1, (int) ($rows[0]['total_fixtures'] ?? $n));

        $accuracy = $n > 0 ? round($won / $n * 100, 2) : null;
        $brier = $this->brier($resolved);
        $logLoss = $this->logLoss($resolved);
        $avgProb = $this->average($resolved, 'probability');
        $avgConf = $this->average($resolved, 'confidence');
        $ece = $this->expectedCalibrationError($resolved);

        $stats = [
            'market_code' => $rows[0]['market_code'] ?? null,
            'market_label' => $this->label($rows[0]['market_code'] ?? ''),
            'model_version' => $rows[0]['model_version'] ?? null,
            'season' => $rows[0]['season'] ?? null,
            'n' => $n,
            'predictions' => $n,
            'wins' => $won,
            'losses' => $losses,
            'accuracy' => $accuracy,
            'coverage' => round(min(100.0, $n / $totalFixtures * 100), 2),
            'brier' => $brier,
            'log_loss' => $logLoss,
            'avg_probability' => $avgProb,
            'avg_confidence' => $avgConf,
            'calibration_error' => $ece,
            'sample_status' => $this->sampleStatus($n),
            'selections' => $this->selectionBreakdown($rows),
            'confidence_tiers' => $this->confidenceTiers($rows, $n),
            'calibration_buckets' => $this->calibrationBuckets($resolved),
            'gate' => $this->gate($rows, $n),
            'gate_comparison' => $this->gateComparison($rows, $n),
        ];

        return $stats;
    }

    /**
     * 1X2 home/draw/away and Double Chance 1x/x2/12 breakdowns.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    protected function selectionBreakdown(array $rows): array
    {
        $code = $rows[0]['market_code'] ?? null;
        $selections = self::SELECTION_GROUPS[$code] ?? [];

        if (empty($selections)) {
            return [];
        }

        $out = [];

        foreach ($selections as $sel) {
            $selRows = array_values(array_filter($rows, fn ($r) => ($r['selection'] ?? null) === $sel));
            $resolved = array_values(array_filter($selRows, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
            $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
            $n = count($resolved);

            $out[$sel] = [
                'selection' => $sel,
                'n' => $n,
                'wins' => $won,
                'losses' => $n - $won,
                'accuracy' => $n > 0 ? round($won / $n * 100, 2) : null,
            ];
        }

        return $out;
    }

    /**
     * Confidence tiers (50+, 60+, 70+, 80+, 90+) for one league x market cell.
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    protected function confidenceTiers(array $rows, int $totalN): array
    {
        $tiers = config('evaluation.matrix.confidence_tiers', [50, 60, 70, 80, 90]);
        $out = [];

        foreach ($tiers as $min) {
            $filtered = array_values(array_filter($rows, fn ($r) => ($r['confidence'] ?? 0) >= $min));
            $resolved = array_values(array_filter($filtered, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
            $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
            $n = count($resolved);

            $out[] = [
                'min' => $min,
                'n' => $n,
                'wins' => $won,
                'losses' => $n - $won,
                'accuracy' => $n > 0 ? round($won / $n * 100, 2) : null,
                'coverage' => $totalN > 0 ? round($n / $totalN * 100, 2) : null,
            ];
        }

        return $out;
    }

    /**
     * Gate evaluation at the configured publication gate.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    protected function gate(array $rows, int $totalN): array
    {
        $gate = config('evaluation.matrix.publication_gate', ['min_probability' => 70, 'min_confidence' => 75]);

        return $this->gateAt($rows, $totalN, (int) $gate['min_probability'], (int) $gate['min_confidence']);
    }

    /**
     * Gate evaluation for an explicit probability/confidence pair.
     *
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    protected function gateAt(array $rows, int $totalN, int $minProbability, int $minConfidence): array
    {
        $filtered = array_values(array_filter(
            $rows,
            fn ($r) => ($r['probability'] ?? 0) >= $minProbability && ($r['confidence'] ?? 0) >= $minConfidence
        ));
        $resolved = array_values(array_filter($filtered, fn ($r) => in_array($r['result'] ?? null, ['won', 'lost'], true)));
        $won = count(array_filter($resolved, fn ($r) => $r['result'] === 'won'));
        $n = count($resolved);

        return [
            'min_probability' => $minProbability,
            'min_confidence' => $minConfidence,
            'n' => $n,
            'wins' => $won,
            'losses' => $n - $won,
            'accuracy' => $n > 0 ? round($won / $n * 100, 2) : null,
            'coverage' => $totalN > 0 ? round($n / $totalN * 100, 2) : null,
        ];
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    protected function gateComparison(array $rows, int $totalN): array
    {
        $pairs = config('evaluation.matrix.gate_comparison', [[60, 60], [65, 65], [70, 70], [70, 75], [75, 75], [80, 80]]);
        $out = [];

        foreach ($pairs as $pair) {
            [$p, $c] = $pair;
            $result = $this->gateAt($rows, $totalN, (int) $p, (int) $c);
            $result['label'] = "{$p}/{$c}";
            $out[] = $result;
        }

        return $out;
    }

    /**
     * Calibration buckets (50-59 … 90-100): sample size, average predicted
     * probability, actual success rate and the calibration gap.
     *
     * @param list<array<string,mixed>> $resolved
     * @return list<array<string,mixed>>
     */
    protected function calibrationBuckets(array $resolved): array
    {
        $edges = config('evaluation.probability_buckets', [50, 60, 70, 80, 90, 100]);
        $out = [];

        for ($i = 0; $i < count($edges) - 1; $i++) {
            $low = $edges[$i];
            $high = $edges[$i + 1];
            $inclusiveHigh = $i === count($edges) - 2;

            $bucket = array_values(array_filter($resolved, function ($r) use ($low, $high, $inclusiveHigh) {
                $v = (float) ($r['probability'] ?? 0);

                return $inclusiveHigh ? ($v >= $low && $v <= 100) : ($v >= $low && $v < $high);
            }));

            $n = count($bucket);
            $won = count(array_filter($bucket, fn ($r) => ($r['result'] ?? null) === 'won'));
            $avgProb = $this->average($bucket, 'probability');
            $actual = $n > 0 ? round($won / $n * 100, 2) : null;

            $out[] = [
                'label' => $inclusiveHigh ? "{$low}-100" : "{$low}-".($high - 1),
                'n' => $n,
                'avg_probability' => $avgProb,
                'actual' => $actual,
                'gap' => ($avgProb !== null && $actual !== null) ? round($avgProb - $actual, 2) : null,
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string,mixed>> $leagues
     * @return array<string,array<string,mixed>>
     */
    protected function marketSummary(array $leagues): array
    {
        $out = [];

        foreach (self::MARKETS as $code) {
            $cells = [];

            foreach ($leagues as $league) {
                $m = $league['markets'][$code] ?? null;

                if ($m && $m['n'] > 0 && $m['accuracy'] !== null) {
                    $cells[] = $m;
                }
            }

            if (empty($cells)) {
                continue;
            }

            $accuracies = array_map(fn ($m) => (float) $m['accuracy'], $cells);
            $briers = array_map(fn ($m) => (float) ($m['brier'] ?? 0), $cells);
            $totalN = array_sum(array_map(fn ($m) => $m['n'], $cells));
            $totalWon = array_sum(array_map(fn ($m) => $m['wins'], $cells));

            $out[$code] = [
                'market_code' => $code,
                'market_label' => $this->label($code),
                'leagues_evaluated' => count($cells),
                'total_n' => $totalN,
                'total_wins' => $totalWon,
                'pooled_accuracy' => $totalN > 0 ? round($totalWon / $totalN * 100, 2) : null,
                'mean_accuracy' => round($this->mean($accuracies), 2),
                'median_accuracy' => round($this->median($accuracies), 2),
                'std_accuracy' => round($this->stddev($accuracies), 2),
                'min_accuracy' => round(min($accuracies), 2),
                'max_accuracy' => round(max($accuracies), 2),
                'mean_brier' => round($this->mean($briers), 4),
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string,mixed>> $leagues
     * @return list<array<string,mixed>>
     */
    protected function leagueSummary(array $leagues): array
    {
        $out = [];

        foreach ($leagues as $league) {
            $accuracies = [];
            $briers = [];

            foreach ($league['markets'] as $m) {
                if ($m['accuracy'] !== null) {
                    $accuracies[] = (float) $m['accuracy'];
                }

                if ($m['brier'] !== null) {
                    $briers[] = (float) $m['brier'];
                }
            }

            $out[] = [
                'league_id' => $league['league_id'],
                'league_name' => $league['league_name'],
                'markets' => count($league['markets']),
                'mean_accuracy' => round($this->mean($accuracies), 2),
                'median_accuracy' => round($this->median($accuracies), 2),
                'std_accuracy' => round($this->stddev($accuracies), 2),
                'mean_brier' => round($this->mean($briers), 4),
            ];
        }

        usort($out, fn ($a, $b) => ($b['mean_accuracy'] ?? 0) <=> ($a['mean_accuracy'] ?? 0));

        return $out;
    }

    /**
     * Ranked league x market combinations by a composite score (accuracy,
     * Brier, coverage, calibration and sample size) — never accuracy alone.
     *
     * @param list<array<string,mixed>> $leagues
     * @return list<array<string,mixed>>
     */
    protected function ranking(array $leagues, string $version): array
    {
        $weights = config('evaluation.matrix.ranking_weights', [
            'accuracy' => 0.30,
            'brier' => 0.25,
            'coverage' => 0.15,
            'calibration' => 0.20,
            'sample' => 0.10,
        ]);

        $adequate = (int) config('evaluation.matrix.adequate_sample_threshold', 100);
        $combos = [];

        foreach ($leagues as $league) {
            foreach ($league['markets'] as $code => $m) {
                $accuracy = (float) ($m['accuracy'] ?? 0);
                $brier = (float) ($m['brier'] ?? 0.5);
                $coverage = (float) ($m['coverage'] ?? 0);
                $ece = (float) ($m['calibration_error'] ?? 0);
                $n = (int) $m['n'];

                $score = ($weights['accuracy'] ?? 0.30) * ($accuracy / 100)
                    + ($weights['brier'] ?? 0.25) * (1 - min(1.0, $brier))
                    + ($weights['coverage'] ?? 0.15) * ($coverage / 100)
                    + ($weights['calibration'] ?? 0.20) * (1 - min(1.0, $ece / 100))
                    + ($weights['sample'] ?? 0.10) * min(1.0, $n / max(1, $adequate));

                $combos[] = [
                    'league_id' => $league['league_id'],
                    'league' => $league['league_name'],
                    'market' => $code,
                    'market_label' => $this->label($code),
                    'model' => $version,
                    'accuracy' => $m['accuracy'],
                    'coverage' => $m['coverage'],
                    'brier' => $m['brier'],
                    'calibration' => $m['calibration_error'],
                    'n' => $n,
                    'sample_status' => $m['sample_status'],
                    'score' => round($score * 100, 2),
                ];
            }
        }

        usort($combos, function ($a, $b) {
            if ($a['sample_status'] !== $b['sample_status']) {
                $order = ['ADEQUATE' => 0, 'LOW' => 1, 'INSUFFICIENT' => 2];

                return ($order[$a['sample_status']] ?? 9) <=> ($order[$b['sample_status']] ?? 9);
            }

            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        foreach ($combos as $i => $c) {
            $combos[$i]['rank'] = $i + 1;
        }

        return $combos;
    }

    /**
     * @param array<string,mixed> $perModel
     * @param array<int,string> $leagueNames
     * @return array<string,mixed>
     */
    protected function attachCandidates(array $perModel, array $leagueNames): array
    {
        $cfg = config('evaluation.matrix.candidate', []);
        $minAccuracy = (float) ($cfg['min_accuracy'] ?? 55.0);
        $maxBrier = (float) ($cfg['max_brier'] ?? 0.25);
        $minGateSample = (int) ($cfg['min_gate_sample'] ?? 50);
        $adequate = (int) config('evaluation.matrix.adequate_sample_threshold', 100);

        foreach ($perModel as $version => $model) {
            $model['publication_candidates'] = [];

            foreach ($model['leagues'] as $league) {
                foreach ($league['markets'] as $code => $m) {
                    $gate = $m['gate'] ?? [];
                    $recommended = $this->recommendedGate($m['gate_comparison'] ?? [], $minGateSample);

                    // Candidate rule: ADEQUATE sample, accuracy above the bar
                    // and Brier below the bar. The publication gate is reported
                    // as supporting evidence, not as a candidate filter, because
                    // the gate itself is what this phase is evaluating.
                    $isCandidate = ($m['n'] >= $adequate)
                        && ($m['accuracy'] !== null && $m['accuracy'] >= $minAccuracy)
                        && ($m['brier'] !== null && $m['brier'] <= $maxBrier);

                    $candidate = [
                        'key' => implode('|', [$league['league_id'], $code, $version]),
                        'league_id' => $league['league_id'],
                        'league' => $league['league_name'],
                        'market' => $code,
                        'market_label' => $this->label($code),
                        'model' => $version,
                        'accuracy' => $m['accuracy'],
                        'coverage' => $m['coverage'],
                        'brier' => $m['brier'],
                        'calibration' => $m['calibration_error'],
                        'n' => $m['n'],
                        'sample_status' => $m['sample_status'],
                        'gate_accuracy' => $gate['accuracy'] ?? null,
                        'gate_n' => $gate['n'] ?? 0,
                        'gate_coverage' => $gate['coverage'] ?? null,
                        'gate_label' => "{$gate['min_probability']}/{$gate['min_confidence']}",
                        'recommended_gate' => $recommended ? "{$recommended['min_probability']}/{$recommended['min_confidence']}" : null,
                        'recommended_gate_accuracy' => $recommended['accuracy'] ?? null,
                        'recommended_gate_n' => $recommended['n'] ?? null,
                        'is_candidate' => $isCandidate,
                        'status' => 'CANDIDATE',
                    ];

                    if ($isCandidate) {
                        $model['publication_candidates'][] = $candidate;
                    }
                }
            }

            // Overlay persisted admin status where present.
            $leagueIds = array_column($model['leagues'], 'league_id');

            if (! empty($leagueIds)) {
                $persisted = PublicationCandidate::query()
                    ->whereIn('league_id', $leagueIds)
                    ->get();

                foreach ($model['publication_candidates'] as $i => $candidate) {
                    foreach ($persisted as $p) {
                        if ((int) $p->league_id === (int) $candidate['league_id']
                            && $p->market_code === $candidate['market']
                            && $p->model_version === $candidate['model']) {
                            $model['publication_candidates'][$i]['status'] = strtoupper($p->status);
                            $model['publication_candidates'][$i]['approved_at'] = $p->approved_at?->toDateTimeString();
                        }
                    }
                }
            }

            $perModel[$version] = $model;
        }

        return $perModel;
    }

    /**
     * Pick the best alternative gate with at least the minimum sample size.
     *
     * @param list<array<string,mixed>> $comparison
     * @return array<string,mixed>|null
     */
    protected function recommendedGate(array $comparison, int $minSample): ?array
    {
        $eligible = array_values(array_filter($comparison, fn ($g) => ($g['n'] ?? 0) >= $minSample && ($g['accuracy'] ?? null) !== null));

        if (empty($eligible)) {
            return null;
        }

        usort($eligible, function ($a, $b) {
            if (($a['accuracy'] ?? 0) !== ($b['accuracy'] ?? 0)) {
                return ($b['accuracy'] ?? 0) <=> ($a['accuracy'] ?? 0);
            }

            return ($b['n'] ?? 0) <=> ($a['n'] ?? 0);
        });

        return $eligible[0];
    }

    /**
     * Model comparison: v1.x vs v1.0 deltas. Versions without backtest data
     * are reported as NOT AVAILABLE with a reason.
     *
     * @param list<string> $versions
     * @param array<string,mixed> $perModel
     * @return array<string,mixed>
     */
    protected function modelComparison(array $versions, array $perModel): array
    {
        $out = [];

        foreach ($versions as $version) {
            $model = $perModel[$version] ?? null;
            $available = $model['available'] ?? false;

            $summary = [
                'version' => $version,
                'available' => $available,
                'runs' => $model['runs'] ?? 0,
                'leagues' => count($model['leagues'] ?? []),
                'pooled_accuracy' => null,
                'mean_brier' => null,
                'mean_log_loss' => null,
                'mean_calibration_error' => null,
                'note' => $available ? null : 'No completed backtest runs exist for this version. The BacktestEngine applies the un-calibrated ensemble; v1.1.0 calibration is only wired into the live PredictionEngine, so it cannot be backtested without new engine support.',
            ];

            if ($available) {
                $cells = [];

                foreach ($model['leagues'] as $league) {
                    foreach ($league['markets'] as $m) {
                        if ($m['accuracy'] !== null) {
                            $cells[] = $m;
                        }
                    }
                }

                $totalN = array_sum(array_map(fn ($m) => $m['n'], $cells));
                $totalWon = array_sum(array_map(fn ($m) => $m['wins'], $cells));
                $briers = array_filter(array_map(fn ($m) => $m['brier'], $cells), fn ($v) => $v !== null);
                $logLosses = array_filter(array_map(fn ($m) => $m['log_loss'], $cells), fn ($v) => $v !== null);
                $eces = array_filter(array_map(fn ($m) => $m['calibration_error'], $cells), fn ($v) => $v !== null);

                $summary['pooled_accuracy'] = $totalN > 0 ? round($totalWon / $totalN * 100, 2) : null;
                $summary['mean_brier'] = count($briers) ? round(array_sum($briers) / count($briers), 4) : null;
                $summary['mean_log_loss'] = count($logLosses) ? round(array_sum($logLosses) / count($logLosses), 4) : null;
                $summary['mean_calibration_error'] = count($eces) ? round(array_sum($eces) / count($eces), 2) : null;
            }

            $out[] = $summary;
        }

        return $out;
    }

    /**
     * Markets that are consistently strong or weak across leagues, derived
     * from data only.
     *
     * @param list<string> $versions
     * @param array<string,mixed> $perModel
     * @return list<array<string,mixed>>
     */
    protected function strongWeakMarkets(array $versions, array $perModel, string $kind): array
    {
        $rows = [];

        foreach ($versions as $version) {
            $model = $perModel[$version] ?? null;

            if (! ($model['available'] ?? false)) {
                continue;
            }

            foreach ($model['market_summary'] as $code => $s) {
                if ($s['leagues_evaluated'] < 2) {
                    continue;
                }

                $rows[] = [
                    'market_code' => $code,
                    'market_label' => $this->label($code),
                    'mean_accuracy' => $s['mean_accuracy'],
                    'std_accuracy' => $s['std_accuracy'],
                    'mean_brier' => $s['mean_brier'],
                    'leagues_evaluated' => $s['leagues_evaluated'],
                    'model' => $version,
                ];
            }
        }

        // Deduplicate by market (prefer first version with data).
        $deduped = [];

        foreach ($rows as $row) {
            if (! isset($deduped[$row['market_code']])) {
                $deduped[$row['market_code']] = $row;
            }
        }

        $rows = array_values($deduped);

        if ($kind === 'strong') {
            usort($rows, function ($a, $b) {
                if (($a['mean_accuracy'] ?? 0) !== ($b['mean_accuracy'] ?? 0)) {
                    return ($b['mean_accuracy'] ?? 0) <=> ($a['mean_accuracy'] ?? 0);
                }

                return ($a['mean_brier'] ?? 1) <=> ($b['mean_brier'] ?? 1);
            });
        } else {
            usort($rows, function ($a, $b) {
                if (($a['mean_accuracy'] ?? 0) !== ($b['mean_accuracy'] ?? 0)) {
                    return ($a['mean_accuracy'] ?? 0) <=> ($b['mean_accuracy'] ?? 0);
                }

                return ($b['mean_brier'] ?? 0) <=> ($a['mean_brier'] ?? 0);
            });
        }

        return $rows;
    }

    protected function sampleStatus(int $n): string
    {
        $insufficient = (int) config('evaluation.matrix.insufficient_sample_threshold', 50);
        $adequate = (int) config('evaluation.matrix.adequate_sample_threshold', 100);

        if ($n < $insufficient) {
            return 'INSUFFICIENT';
        }

        if ($n < $adequate) {
            return 'LOW';
        }

        return 'ADEQUATE';
    }

    protected function brier(array $resolved): ?float
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

    protected function logLoss(array $resolved): ?float
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

    protected function average(array $rows, string $key): ?float
    {
        if (empty($rows)) {
            return null;
        }

        return round(array_sum(array_map(fn ($r) => (float) ($r[$key] ?? 0), $rows)) / count($rows), 2);
    }

    protected function expectedCalibrationError(array $resolved): ?float
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

                return $inclusiveHigh ? ($v >= $low && $v <= 100) : ($v >= $low && $v < $high);
            }));

            $n = count($bucket);

            if ($n === 0) {
                continue;
            }

            $won = count(array_filter($bucket, fn ($r) => ($r['result'] ?? null) === 'won'));
            $accuracy = $won / $n * 100;
            $avgProb = array_sum(array_map(fn ($r) => (float) ($r['probability'] ?? 0), $bucket)) / $n;

            $ece += ($n / $total) * abs($avgProb - $accuracy);
        }

        return round($ece, 2);
    }

    protected function mean(array $values): float
    {
        return count($values) ? array_sum($values) / count($values) : 0.0;
    }

    protected function median(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        sort($values);
        $n = count($values);
        $mid = intdiv($n, 2);

        return $n % 2 === 1 ? $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }

    protected function stddev(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $mean = $this->mean($values);
        $sumSq = 0.0;

        foreach ($values as $v) {
            $sumSq += ($v - $mean) ** 2;
        }

        return sqrt($sumSq / (count($values) - 1));
    }
}
