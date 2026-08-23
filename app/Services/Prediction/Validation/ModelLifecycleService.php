<?php

namespace App\Services\Prediction\Validation;

use App\Models\ModelAuditLog;
use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Manages the production model lifecycle (Phase 1G).
 *
 * States: candidate → shadow → approved → active → retired (rejected anytime).
 *
 * Activation is gated: a model may only become ACTIVE if it is APPROVED and
 * has accumulated enough resolved shadow predictions. Every transition is
 * recorded immutably in model_audit_logs.
 */
class ModelLifecycleService
{
    /**
     * Approve a candidate/shadow model (does not activate it).
     */
    public function approve(PredictionModel $model, User $admin, ?string $reason = null): PredictionModel
    {
        return $this->transition($model, PredictionModel::STATUS_APPROVED, 'approved', $admin, $reason);
    }

    /**
     * Reject a model.
     */
    public function reject(PredictionModel $model, User $admin, ?string $reason = null): PredictionModel
    {
        return $this->transition($model, PredictionModel::STATUS_REJECTED, 'rejected', $admin, $reason);
    }

    /**
     * Retire a model (only meaningful for a previously active/approved model).
     */
    public function retire(PredictionModel $model, User $admin, ?string $reason = null): PredictionModel
    {
        $previous = $model->status;

        DB::transaction(function () use ($model, $admin, $reason, $previous) {
            $model->update([
                'status' => PredictionModel::STATUS_RETIRED,
                'active' => false,
            ]);

            $this->log($model, 'retired', $previous, PredictionModel::STATUS_RETIRED, $admin, $reason);
        });

        return $model->fresh();
    }

    /**
     * Activate a model. Fails with a DomainException unless every gate check
     * passes. Exactly one model remains ACTIVE: the previous active model is
     * retired in the same transaction.
     */
    public function activate(PredictionModel $model, User $admin, ?string $reason = null): PredictionModel
    {
        $failed = array_values(array_filter($this->activationChecks($model), fn ($c) => ! $c['passed']));

        if (! empty($failed)) {
            $reasons = implode('; ', array_map(fn ($c) => $c['detail'], $failed));

            throw new \DomainException('Activation rejected: '.$reasons);
        }

        DB::transaction(function () use ($model, $admin, $reason) {
            $previousActive = PredictionModel::where('active', true)->first();

            if ($previousActive && $previousActive->id !== $model->id) {
                $previousActive->update([
                    'status' => PredictionModel::STATUS_RETIRED,
                    'active' => false,
                ]);

                $this->log($previousActive, 'retired', PredictionModel::STATUS_ACTIVE, PredictionModel::STATUS_RETIRED, $admin, 'Replaced by '.$model->version);
            }

            $previous = $model->status;
            $model->update([
                'status' => PredictionModel::STATUS_ACTIVE,
                'active' => true,
            ]);

            $this->log($model, 'activated', $previous, PredictionModel::STATUS_ACTIVE, $admin, $reason);
        });

        return $model->fresh();
    }

    /**
     * The activation gate checks (configurable thresholds).
     *
     * @return list<array{name:string,passed:bool,detail:string}>
     */
    public function activationChecks(PredictionModel $model): array
    {
        $gate = config('evaluation.model_gate', []);
        $minShadow = (int) ($gate['minimum_shadow_predictions'] ?? 500);
        $shadowCount = $this->shadowResolvedCount($model);

        $checks = [];

        $checks[] = [
            'name' => 'status_approved',
            'passed' => $model->status === PredictionModel::STATUS_APPROVED,
            'detail' => $model->status === PredictionModel::STATUS_APPROVED
                ? 'Model is approved.'
                : "Model status must be 'approved' (currently '{$model->status}').",
        ];

        $checks[] = [
            'name' => 'shadow_sample',
            'passed' => $shadowCount >= $minShadow,
            'detail' => $shadowCount >= $minShadow
                ? "Shadow sample sufficient ({$shadowCount} resolved)."
                : "Insufficient shadow sample: {$shadowCount} resolved (minimum {$minShadow}).",
        ];

        return $checks;
    }

    /**
     * Rollback: revert production to a previous model version with one
     * controlled configuration change. This is a safety operation to a proven
     * baseline and deliberately bypasses the promotion shadow-sample gate.
     */
    public function rollback(PredictionModel $model, User $admin, ?string $reason = null): PredictionModel
    {
        DB::transaction(function () use ($model, $admin, $reason) {
            $previousActive = PredictionModel::where('active', true)->first();

            if ($previousActive && $previousActive->id !== $model->id) {
                $previousActive->update([
                    'status' => PredictionModel::STATUS_RETIRED,
                    'active' => false,
                ]);

                $this->log($previousActive, 'retired', PredictionModel::STATUS_ACTIVE, PredictionModel::STATUS_RETIRED, $admin, 'Rolled back to '.$model->version);
            }

            $previous = $model->status;
            $model->update([
                'status' => PredictionModel::STATUS_ACTIVE,
                'active' => true,
            ]);

            $this->log($model, 'rollback', $previous, PredictionModel::STATUS_ACTIVE, $admin, $reason ?? 'Manual rollback');
        });

        return $model->fresh();
    }

    /**
     * Number of resolved shadow predictions for a model version.
     */
    public function shadowResolvedCount(PredictionModel $model): int
    {
        return Prediction::where('model_version', $model->version)
            ->whereNotNull('result')
            ->count();
    }

    protected function transition(PredictionModel $model, string $to, string $action, User $admin, ?string $reason): PredictionModel
    {
        $previous = $model->status;

        DB::transaction(function () use ($model, $to, $action, $admin, $reason, $previous) {
            $model->update(['status' => $to]);
            $this->log($model, $action, $previous, $to, $admin, $reason);
        });

        return $model->fresh();
    }

    protected function log(PredictionModel $model, string $action, ?string $from, ?string $to, User $admin, ?string $reason): void
    {
        ModelAuditLog::create([
            'prediction_model_id' => $model->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'admin_id' => $admin->id,
            'reason' => $reason,
        ]);
    }
}
