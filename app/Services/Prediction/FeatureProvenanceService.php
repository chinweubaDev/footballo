<?php

namespace App\Services\Prediction;

use App\Models\Prediction;

/**
 * Phase 1K — feature provenance validation.
 *
 * Verifies the temporal integrity of a prediction's inputs:
 *
 *   feature_data_timestamp <= prediction_generated_at < kickoff
 *
 * If timestamps cannot be established, the prediction is flagged
 * PROVENANCE_UNCERTAIN rather than silently treated as valid.
 */
class FeatureProvenanceService
{
    public const VALID = 'valid';
    public const INVALID = 'invalid';
    public const UNCERTAIN = 'provenance_uncertain';

    /**
     * @return array{status:string,reason:string}
     */
    public function check(Prediction $prediction): array
    {
        $generatedAt = $prediction->prediction_generated_at;
        $featureAt = $prediction->feature_data_timestamp;
        $kickoff = $prediction->fixture?->match_date;

        if ($generatedAt === null || $featureAt === null || $kickoff === null) {
            return [
                'status' => self::UNCERTAIN,
                'reason' => 'Timestamps cannot be fully established (generated/feature/kickoff missing).',
            ];
        }

        if ($featureAt->gt($generatedAt)) {
            return [
                'status' => self::INVALID,
                'reason' => 'Feature data timestamp is after prediction generation time.',
            ];
        }

        if (! $generatedAt->lt($kickoff)) {
            return [
                'status' => self::INVALID,
                'reason' => 'Prediction was generated at or after kickoff.',
            ];
        }

        return [
            'status' => self::VALID,
            'reason' => 'feature_data_timestamp <= prediction_generated_at < kickoff.',
        ];
    }
}
