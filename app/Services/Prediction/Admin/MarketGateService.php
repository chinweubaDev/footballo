<?php

namespace App\Services\Prediction\Admin;

use App\Models\PredictionCategory;
use App\Models\PredictionGateAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin workflow for per-market publication gates (Phase 1G.1 §14-16).
 *
 * Thresholds are NEVER changed automatically. An admin reviews the measured
 * recommendation and explicitly approves or rejects it; every change is
 * written immutably to prediction_gate_audits.
 */
class MarketGateService
{
    /**
     * Apply the recommended thresholds to a market's live gate.
     */
    public function approve(PredictionCategory $category, User $admin, int $minProbability, int $minConfidence, ?string $reason = null): PredictionCategory
    {
        $oldProbability = $category->min_probability;
        $oldConfidence = $category->min_confidence;

        DB::transaction(function () use ($category, $admin, $minProbability, $minConfidence, $reason, $oldProbability, $oldConfidence) {
            $category->update([
                'min_probability' => $minProbability,
                'min_confidence' => $minConfidence,
                'gate_status' => 'approved',
            ]);

            PredictionGateAudit::create([
                'prediction_category_id' => $category->id,
                'market_code' => $category->code,
                'action' => 'approved',
                'old_probability' => $oldProbability,
                'new_probability' => $minProbability,
                'old_confidence' => $oldConfidence,
                'new_confidence' => $minConfidence,
                'admin_id' => $admin->id,
                'reason' => $reason,
            ]);
        });

        return $category->fresh();
    }

    /**
     * Reject the recommendation without changing the live thresholds.
     */
    public function reject(PredictionCategory $category, User $admin, ?string $reason = null): PredictionCategory
    {
        DB::transaction(function () use ($category, $admin, $reason) {
            $category->update(['gate_status' => 'rejected']);

            PredictionGateAudit::create([
                'prediction_category_id' => $category->id,
                'market_code' => $category->code,
                'action' => 'rejected',
                'old_probability' => $category->min_probability,
                'new_probability' => $category->min_probability,
                'old_confidence' => $category->min_confidence,
                'new_confidence' => $category->min_confidence,
                'admin_id' => $admin->id,
                'reason' => $reason,
            ]);
        });

        return $category->fresh();
    }
}
