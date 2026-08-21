<?php

namespace App\Services\Prediction\Admin;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use App\Models\PredictionOverride;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PredictionAdminService
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function lock(Prediction $prediction, User $admin): Prediction
    {
        DB::transaction(function () use ($prediction, $admin) {
            $prediction->update(['locked_at' => now()]);
            $this->audit->log($prediction, 'admin_lock', [], $admin);
        });

        return $prediction->fresh();
    }

    public function unlock(Prediction $prediction, User $admin): Prediction
    {
        DB::transaction(function () use ($prediction, $admin) {
            $prediction->update(['locked_at' => null]);
            $this->audit->log($prediction, 'admin_unlock', [], $admin);
        });

        return $prediction->fresh();
    }

    public function feature(Prediction $prediction, array $data, User $admin): Prediction
    {
        if (! in_array($prediction->market_code, ['1x2', 'double_chance'], true)) {
            throw new \DomainException('Only 1X2 and Double Chance markets can be featured.');
        }

        DB::transaction(function () use ($prediction, $data, $admin) {
            $prediction->update([
                'featured' => true,
                'admin_featured' => (bool) ($data['admin_featured'] ?? true),
                'featured_priority' => (int) ($data['featured_priority'] ?? 0),
            ]);

            $this->audit->log($prediction, 'admin_feature', $data, $admin);
        });

        return $prediction->fresh();
    }

    public function unfeature(Prediction $prediction, User $admin): Prediction
    {
        DB::transaction(function () use ($prediction, $admin) {
            $prediction->update([
                'featured' => false,
                'admin_featured' => false,
                'featured_priority' => 0,
                'featured_until' => null,
            ]);

            $this->audit->log($prediction, 'admin_unfeature', [], $admin);
        });

        return $prediction->fresh();
    }

    public function dashboardStats(): array
    {
        return [
            'upcoming_fixtures' => Fixture::query()
                ->where('status', 'NS')
                ->whereDate('match_date', '>=', today())
                ->count(),
            'generated' => Prediction::query()->count(),
            'published' => Prediction::query()->where('status', 'published')->count(),
            'no_bet' => Prediction::query()->where('status', 'no_bet')->count(),
            'featured' => Prediction::query()->where('featured', true)->count(),
            'enabled_leagues' => League::query()->where('enabled', true)->count(),
            'enabled_markets' => PredictionCategory::query()->where('enabled', true)->count(),
        ];
    }

    public function latestPredictions(int $limit = 10)
    {
        return Prediction::query()
            ->with('fixture')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function recentOverrides(int $limit = 10)
    {
        return PredictionOverride::query()
            ->with(['prediction.fixture', 'admin'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function activeModel(): ?PredictionModel
    {
        return PredictionModel::query()->where('active', true)->first();
    }
}
