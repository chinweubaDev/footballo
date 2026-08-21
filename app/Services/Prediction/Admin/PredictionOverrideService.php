<?php

namespace App\Services\Prediction\Admin;

use App\Models\Prediction;
use App\Models\PredictionOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Centralizes the admin override + revert workflow. The original model
 * prediction is always preserved and every change is recorded immutably.
 */
class PredictionOverrideService
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function override(Prediction $prediction, string $selection, ?float $probability, string $reason, User $admin): Prediction
    {
        if ($prediction->locked_at !== null) {
            throw new \DomainException('This prediction is locked and cannot be overridden.');
        }

        DB::transaction(function () use ($prediction, $selection, $probability, $reason, $admin) {
            $currentSelection = $prediction->admin_selection ?? $prediction->selection;

            PredictionOverride::create([
                'prediction_id' => $prediction->id,
                'original_selection' => $currentSelection,
                'new_selection' => $selection,
                'original_probability' => (float) $prediction->probability,
                'new_probability' => $probability,
                'reason' => $reason,
                'admin_id' => $admin->id,
            ]);

            $prediction->update([
                'original_selection' => $prediction->original_selection ?? $currentSelection,
                'admin_selection' => $selection,
                'override_reason' => $reason,
                'overridden_by' => $admin->id,
                'overridden_at' => now(),
            ]);

            $this->audit->log($prediction, 'admin_override', [
                'original_selection' => $currentSelection,
                'new_selection' => $selection,
                'new_probability' => $probability,
                'reason' => $reason,
            ], $admin);
        });

        return $prediction->fresh();
    }

    public function revert(Prediction $prediction, User $admin): Prediction
    {
        DB::transaction(function () use ($prediction, $admin) {
            $overriddenFrom = $prediction->admin_selection;
            $revertedTo = $prediction->original_selection ?? $prediction->selection;

            if ($overriddenFrom !== null) {
                PredictionOverride::create([
                    'prediction_id' => $prediction->id,
                    'original_selection' => $overriddenFrom,
                    'new_selection' => $revertedTo,
                    'original_probability' => (float) $prediction->probability,
                    'new_probability' => null,
                    'reason' => 'Reverted to AI prediction',
                    'admin_id' => $admin->id,
                ]);
            }

            $prediction->update([
                'admin_selection' => null,
                'override_reason' => null,
                'overridden_by' => null,
                'overridden_at' => null,
            ]);

            $this->audit->log($prediction, 'admin_revert', [
                'reverted_from' => $overriddenFrom,
                'reverted_to' => $revertedTo,
            ], $admin);
        });

        return $prediction->fresh();
    }
}
