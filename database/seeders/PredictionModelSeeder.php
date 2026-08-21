<?php

namespace Database\Seeders;

use App\Models\PredictionModel;
use Illuminate\Database\Seeder;

class PredictionModelSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate: never clobber an existing model's configuration or
        // status (an admin may have stored calibration or changed state).
        PredictionModel::firstOrCreate(
            ['version' => 'v1.0.0'],
            [
                'name' => 'Esurebet Statistical Ensemble',
                'description' => 'Initial statistical prediction model foundation.',
                'configuration' => null,
                'active' => false,
                'status' => PredictionModel::STATUS_ACTIVE,
            ]
        );

        PredictionModel::firstOrCreate(
            ['version' => 'v1.1.0'],
            [
                'name' => 'Esurebet Calibrated Ensemble',
                'description' => 'Probability-calibrated ensemble (Phase 1F). Shadow mode until validated on held-out data.',
                'configuration' => [
                    'calibration' => [],
                    'thresholds' => [],
                ],
                'active' => false,
                'status' => PredictionModel::STATUS_SHADOW,
            ]
        );

        // Ensure exactly one active model (default to v1.0.0 when none active).
        if (! PredictionModel::where('active', true)->exists()) {
            PredictionModel::where('version', 'v1.0.0')->update([
                'active' => true,
                'status' => PredictionModel::STATUS_ACTIVE,
            ]);
        }
    }
}
