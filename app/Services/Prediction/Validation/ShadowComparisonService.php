<?php

namespace App\Services\Prediction\Validation;

use App\Models\PredictionModel;
use App\Services\Prediction\Calibration\ModelComparisonService;

/**
 * Compares the ACTIVE production model against the SHADOW candidate on
 * resolved predictions, and produces a data-driven recommendation.
 * It never auto-activates — the admin decides.
 */
class ShadowComparisonService
{
    public function __construct(
        protected ModelComparisonService $comparison,
        protected ModelLifecycleService $lifecycle,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function dashboard(): array
    {
        $active = PredictionModel::where('active', true)->first();
        $shadow = PredictionModel::where('status', PredictionModel::STATUS_SHADOW)->first()
            ?? PredictionModel::where('status', PredictionModel::STATUS_APPROVED)->first()
            ?? PredictionModel::where('version', '!=', $active?->version)->orderBy('id', 'desc')->first();

        $data = [
            'active' => null,
            'shadow' => null,
            'recommendation' => 'Insufficient data',
            'minimum_shadow' => (int) config('evaluation.model_gate.minimum_shadow_predictions', 500),
        ];

        if ($active) {
            $data['active'] = [
                'model' => $active->toArray(),
                'performance' => $this->comparison->summarizeVersion($active->version),
            ];
        }

        if ($shadow) {
            $shadowCount = $this->lifecycle->shadowResolvedCount($shadow);
            $data['shadow'] = [
                'model' => $shadow->toArray(),
                'performance' => $this->comparison->summarizeVersion($shadow->version),
                'resolved_count' => $shadowCount,
            ];

            $data['recommendation'] = $this->recommendation($data['active'], $data['shadow'], $shadowCount);
        }

        return $data;
    }

    /**
     * @param array<string,mixed>|null $active
     * @param array<string,mixed>|null $shadow
     */
    protected function recommendation(?array $active, ?array $shadow, int $shadowCount): string
    {
        $minShadow = (int) config('evaluation.model_gate.minimum_shadow_predictions', 500);

        if ($shadowCount < $minShadow) {
            return "Insufficient shadow sample ({$shadowCount}/{$minShadow} resolved). Keep current model.";
        }

        if ($active === null || $shadow === null) {
            return 'Keep current model.';
        }

        $activeAcc = $active['performance']['overview']['accuracy'] ?? null;
        $shadowAcc = $shadow['performance']['overview']['accuracy'] ?? null;

        if ($activeAcc === null || $shadowAcc === null) {
            return 'Insufficient resolved data. Keep current model.';
        }

        $delta = round($shadowAcc - $activeAcc, 2);

        if ($delta > 0.5) {
            return "Candidate model shows improvement (+{$delta} pts accuracy). Review and approve if consistent.";
        }

        return 'Keep current model.';
    }
}
