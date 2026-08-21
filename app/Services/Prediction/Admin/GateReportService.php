<?php

namespace App\Services\Prediction\Admin;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Services\Prediction\Calibration\GateOptimizer;

/**
 * Builds the /admin/predictions/gates payload (Phase 1G.1 §15).
 *
 * It measures the accuracy/coverage/Brier tradeoff from resolved data (backtest
 * preferred, live resolved as fallback), derives per-market recommendations and
 * status, and merges them with each market's current gate configuration.
 */
class GateReportService
{
    public function __construct(protected GateOptimizer $optimizer)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function report(): array
    {
        $rows = $this->loadRows();
        $recommendations = $this->optimizer->recommend($rows);
        $grids = $this->optimizer->grid($rows);

        $markets = [];

        foreach (PredictionCategory::query()->orderBy('sort_order')->get() as $category) {
            $rec = $recommendations[$category->code] ?? [];
            $grid = $grids[$category->code] ?? [];

            $markets[] = [
                'category' => $category,
                'market' => $category->code,
                'name' => $category->name,
                'enabled' => (bool) $category->enabled,
                'current_min_probability' => $category->effective_min_probability,
                'current_min_confidence' => (int) $category->min_confidence,
                'recommended_min_probability' => $rec['recommended_min_probability'] ?? null,
                'recommended_min_confidence' => $rec['recommended_min_confidence'] ?? null,
                'sample_size' => $rec['resolved'] ?? null,
                'accuracy' => $rec['accuracy'] ?? null,
                'coverage_percent' => $rec['coverage_percent'] ?? null,
                'brier_score' => $rec['brier_score'] ?? null,
                'ci_lower' => $rec['ci_lower'] ?? null,
                'ci_upper' => $rec['ci_upper'] ?? null,
                'status' => $rec['recommended_min_probability'] !== null
                    ? ($rec['accuracy'] >= (float) config('evaluation.status_classification.strong_accuracy', 62.0)
                        ? GateOptimizer::STATUS_CURRENT
                        : GateOptimizer::STATUS_PROMISING)
                    : ($rec['resolved'] !== null && $rec['resolved'] < (int) config('evaluation.gate_optimizer.minimum_sample_size', 100)
                        ? GateOptimizer::STATUS_INSUFFICIENT_DATA
                        : GateOptimizer::STATUS_WEAK),
                'reason' => $rec['reason'] ?? null,
                'gate_status' => $category->gate_status ?? 'none',
                'grid' => $grid,
            ];
        }

        return [
            'markets' => $markets,
            'source' => $this->source(),
            'minimum_sample_size' => (int) config('evaluation.gate_optimizer.minimum_sample_size', 100),
            'insufficient_sample_threshold' => (int) config('evaluation.gate_optimizer.insufficient_sample_threshold', 50),
        ];
    }

    /**
     * Resolved prediction rows for gate analysis. Prefers the latest completed
     * backtest run (single coherent dataset); falls back to live resolved rows.
     *
     * @return list<array{market_code:string,probability:float,confidence:int,result:string}>
     */
    public function loadRows(): array
    {
        $run = BacktestRun::query()
            ->where('status', BacktestRun::STATUS_COMPLETED)
            ->latest('id')
            ->first();

        if ($run) {
            return BacktestPrediction::query()
                ->where('backtest_run_id', $run->id)
                ->whereIn('result', ['won', 'lost', 'void'])
                ->get(['market_code', 'probability', 'confidence', 'result'])
                ->map(fn ($p) => [
                    'market_code' => $p->market_code,
                    'probability' => (float) ($p->probability ?? 0),
                    'confidence' => (int) ($p->confidence ?? 0),
                    'result' => $p->result,
                ])
                ->all();
        }

        return Prediction::query()
            ->whereNotNull('result')
            ->whereIn('result', ['won', 'lost', 'void'])
            ->get(['market_code', 'probability', 'confidence', 'result'])
            ->map(fn ($p) => [
                'market_code' => $p->market_code ?? 'unknown',
                'probability' => (float) ($p->probability ?? 0),
                'confidence' => (int) ($p->confidence ?? 0),
                'result' => $p->result,
            ])
            ->all();
    }

    /**
     * @return array<string,mixed>
     */
    protected function source(): array
    {
        $run = BacktestRun::query()
            ->where('status', BacktestRun::STATUS_COMPLETED)
            ->latest('id')
            ->first();

        return [
            'kind' => $run ? 'backtest' : 'live',
            'run_id' => $run?->id,
            'model_version' => $run?->model_version ?? null,
            'league_id' => $run?->league_id ?? null,
            'season' => $run?->season ?? null,
        ];
    }
}
