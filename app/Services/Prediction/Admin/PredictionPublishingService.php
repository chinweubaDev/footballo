<?php

namespace App\Services\Prediction\Admin;

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PredictionPublishingService
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function publish(Prediction $prediction, User $admin): Prediction
    {
        if ($prediction->status === 'no_bet' && $prediction->admin_selection === null) {
            throw new \DomainException('NO_BET predictions cannot be published without an admin override.');
        }

        DB::transaction(function () use ($prediction, $admin) {
            $prediction->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $this->audit->log($prediction, 'admin_publish', [], $admin);
        });

        return $prediction->fresh();
    }

    public function unpublish(Prediction $prediction, User $admin): Prediction
    {
        DB::transaction(function () use ($prediction, $admin) {
            $prediction->update([
                'status' => 'pending_review',
                'published_at' => null,
            ]);

            $this->audit->log($prediction, 'admin_unpublish', [], $admin);
        });

        return $prediction->fresh();
    }
}
