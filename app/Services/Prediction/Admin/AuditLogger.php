<?php

namespace App\Services\Prediction\Admin;

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lightweight structured audit writer for prediction administration.
 * Reuses the existing prediction_logs table (never logs credentials).
 */
class AuditLogger
{
    public function log(Prediction $prediction, string $action, array $data = [], ?User $admin = null): void
    {
        DB::table('prediction_logs')->insert([
            'fixture_id' => $prediction->fixture_id,
            'prediction_id' => $prediction->id,
            'action' => $action,
            'data' => json_encode(array_merge($data, [
                'admin_id' => $admin?->id,
                'admin_name' => $admin?->name,
            ])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
