<?php

namespace App\Services\Prediction\Calibration;

use App\Models\BacktestPrediction;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Services\Prediction\Evaluation\MetricsCalculator;

/**
 * Aggregates per-model-version performance across live predictions and
 * backtest predictions so the admin can compare v1.0.0 vs v1.1.0.
 */
class ModelComparisonService
{
    public function __construct(protected MetricsCalculator $metrics)
    {
    }

    /**
     * All known model versions and their performance.
     *
     * @return array<string,array<string,mixed>>
     */
    public function versions(): array
    {
        $versions = array_unique(array_merge(
            Prediction::query()->whereNotNull('result')->distinct()->pluck('model_version')->all(),
            BacktestPrediction::query()->whereNotNull('result')->distinct()->pluck('model_version')->all(),
        ));

        sort($versions);

        $out = [];

        foreach ($versions as $version) {
            if ($version === null || $version === '') {
                continue;
            }

            $out[$version] = $this->summarizeVersion($version);
        }

        return $out;
    }

    /**
     * @return array<string,mixed>
     */
    public function summarizeVersion(string $version): array
    {
        $rows = $this->rowsForVersion($version);
        $bettable = array_values(array_filter($rows, fn ($r) => ($r['status'] ?? null) !== 'no_bet'));

        return [
            'overview' => $this->metrics->summarize($bettable),
            'by_market' => $this->metrics->byMarket($bettable),
            'calibration' => $this->metrics->probabilityBuckets($bettable),
            'confidence' => $this->metrics->confidenceBuckets($bettable),
            'selectivity' => $this->metrics->selectivity($bettable),
            'live_count' => Prediction::where('model_version', $version)->whereNotNull('result')->count(),
            'backtest_count' => BacktestPrediction::where('model_version', $version)->whereNotNull('result')->count(),
        ];
    }

    /**
     * Resolved rows (live + backtest) for a model version.
     *
     * @return list<array<string,mixed>>
     */
    protected function rowsForVersion(string $version): array
    {
        $rows = [];

        Prediction::query()
            ->where('model_version', $version)
            ->whereNotNull('result')
            ->select(['market_code', 'probability', 'confidence', 'model_version', 'league_id', 'data_quality_score', 'result', 'status'])
            ->chunkById(500, function ($predictions) use (&$rows) {
                foreach ($predictions as $p) {
                    $rows[] = [
                        'market_code' => $p->market_code ?? 'unknown',
                        'probability' => (float) ($p->probability ?? 0),
                        'confidence' => (int) ($p->confidence ?? 0),
                        'model_version' => $p->model_version,
                        'league_id' => $p->league_id,
                        'data_quality_score' => $p->data_quality_score,
                        'result' => $p->result,
                        'status' => $p->status,
                    ];
                }
            });

        BacktestPrediction::query()
            ->where('model_version', $version)
            ->whereNotNull('result')
            ->select(['market_code', 'probability', 'confidence', 'model_version', 'data_quality_score', 'result', 'status'])
            ->chunkById(500, function ($predictions) use (&$rows) {
                foreach ($predictions as $p) {
                    $rows[] = [
                        'market_code' => $p->market_code ?? 'unknown',
                        'probability' => (float) ($p->probability ?? 0),
                        'confidence' => (int) ($p->confidence ?? 0),
                        'model_version' => $p->model_version,
                        'league_id' => null,
                        'data_quality_score' => $p->data_quality_score,
                        'result' => $p->result,
                        'status' => $p->status,
                    ];
                }
            });

        return $rows;
    }

    /**
     * List of registered models (for the admin model list page).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,PredictionModel>
     */
    public function models()
    {
        return PredictionModel::orderBy('version')->get();
    }
}
