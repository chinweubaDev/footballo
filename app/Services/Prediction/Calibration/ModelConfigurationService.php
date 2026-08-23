<?php

namespace App\Services\Prediction\Calibration;

use App\Models\PredictionModel;

/**
 * Single source of truth for how a model version's configuration is resolved.
 *
 * Both the live PredictionEngine and the BacktestEngine use this service so
 * that a given model version always runs the exact same weights and the exact
 * same per-market probability calibration. This is the Phase 1H equivalence
 * guarantee: no model version silently falls back to another version's
 * configuration.
 */
class ModelConfigurationService
{
    /**
     * Resolve the ensemble weights for a model version.
     *
     * Precedence: model configuration `weights` -> global ensemble weights.
     *
     * @return array<string,float>
     */
    public function resolveWeights(?PredictionModel $model): array
    {
        $configuration = $model?->configuration;

        if (is_array($configuration) && isset($configuration['weights']) && is_array($configuration['weights'])) {
            return $configuration['weights'];
        }

        return config('prediction.ensemble.weights', []);
    }

    /**
     * Build per-market probability calibrators from a model's configuration.
     *
     * Models without calibration (e.g. v1.0.0) yield an empty map and their
     * probabilities are therefore left raw — exactly matching production.
     *
     * @return array<string,ProbabilityCalibrator>
     */
    public function calibrators(?PredictionModel $model): array
    {
        $configuration = $model?->configuration ?? [];

        if (! is_array($configuration) || empty($configuration['calibration'])) {
            return [];
        }

        $calibrators = [];

        foreach ($configuration['calibration'] as $market => $parameters) {
            if (is_array($parameters) && isset($parameters['method'])) {
                $calibrators[$market] = ProbabilityCalibrator::fromParameters($parameters);
            }
        }

        return $calibrators;
    }

    /**
     * Whether this model version defines any probability calibration.
     */
    public function hasCalibration(?PredictionModel $model): bool
    {
        $configuration = $model?->configuration ?? [];

        return is_array($configuration) && ! empty($configuration['calibration']);
    }

    /**
     * Calibration version label for a model version.
     *
     * @return string|null
     */
    public function calibrationVersion(?PredictionModel $model): ?string
    {
        $configuration = $model?->configuration ?? [];

        if (! is_array($configuration) || empty($configuration['calibration'])) {
            return null;
        }

        return $configuration['calibration_meta']['calibrated_at'] ?? 'pre-trained';
    }
}
