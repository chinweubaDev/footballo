<?php

namespace App\Services\Prediction\Admin;

use App\Models\PredictionCategory;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MarketPredictionSettingsService
{
    public function toggleEnabled(PredictionCategory $category, User $admin): PredictionCategory
    {
        $category->update(['enabled' => ! $category->enabled]);

        Log::info('Prediction market enabled toggled', [
            'category_id' => $category->id,
            'enabled' => $category->enabled,
            'admin_id' => $admin->id,
        ]);

        return $category->fresh();
    }

    public function update(PredictionCategory $category, array $data, User $admin): PredictionCategory
    {
        $fields = array_filter([
            'min_confidence' => $data['min_confidence'] ?? null,
            'min_probability' => $data['min_probability'] ?? null,
            'minimum_sample_size' => $data['minimum_sample_size'] ?? null,
            'homepage_enabled' => $data['homepage_enabled'] ?? null,
            'sort_order' => $data['sort_order'] ?? null,
        ], fn ($value) => $value !== null);

        $category->update($fields);

        Log::info('Prediction market settings updated', [
            'category_id' => $category->id,
            'admin_id' => $admin->id,
            'changes' => $fields,
        ]);

        return $category->fresh();
    }
}
