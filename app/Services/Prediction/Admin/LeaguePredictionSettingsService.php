<?php

namespace App\Services\Prediction\Admin;

use App\Models\League;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeaguePredictionSettingsService
{
    public function toggleEnabled(League $league, User $admin): League
    {
        $league->update(['enabled' => ! $league->enabled]);

        Log::info('League enabled toggled', [
            'league_id' => $league->id,
            'enabled' => $league->enabled,
            'admin_id' => $admin->id,
        ]);

        return $league->fresh();
    }

    public function update(League $league, array $data, User $admin): League
    {
        $fields = array_filter([
            'season' => $data['season'] ?? null,
            'prediction_min_confidence' => $data['prediction_min_confidence'] ?? null,
            'priority' => $data['priority'] ?? null,
            'prediction_enabled' => $data['prediction_enabled'] ?? null,
            'homepage_enabled' => $data['homepage_enabled'] ?? null,
            'auto_publish' => $data['auto_publish'] ?? null,
        ], fn ($value) => $value !== null);

        $league->update($fields);

        Log::info('League prediction settings updated', [
            'league_id' => $league->id,
            'admin_id' => $admin->id,
            'changes' => $fields,
        ]);

        return $league->fresh();
    }
}
