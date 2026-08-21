<?php

namespace App\Services\Prediction;

use App\Services\Prediction\Support\PredictionCacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Homepage prediction queries with hard business rules enforced server-side:
 *
 *   Sure Picks Tips    -> ONLY market_code = 1x2
 *   Most Featured      -> ONLY market_code = 1x2 or double_chance
 */
class HomepagePredictionService
{
    public function __construct(protected PublicPredictionService $public)
    {
    }

    /**
     * 1X2-only sure picks.
     */
    public function surePicks(int $limit = 6): Collection
    {
        return Cache::remember(PredictionCacheKeys::SURE_PICKS, now()->addMinutes(5), function () use ($limit) {
            return $this->homepagePredictions(['1x2'], $limit);
        });
    }

    /**
     * 1X2 + Double Chance featured selections.
     */
    public function featuredSelections(int $limit = 6): Collection
    {
        return Cache::remember(PredictionCacheKeys::FEATURED, now()->addMinutes(5), function () use ($limit) {
            return $this->homepagePredictions(['1x2', 'double_chance'], $limit);
        });
    }

    protected function homepagePredictions(array $marketCodes, int $limit): Collection
    {
        $enabledMarkets = \App\Models\PredictionCategory::query()
            ->where('enabled', true)
            ->where('homepage_enabled', true)
            ->whereIn('code', $marketCodes)
            ->pluck('code')
            ->all();

        if (empty($enabledMarkets)) {
            return collect();
        }

        return \App\Models\Prediction::query()
            ->where('status', 'published')
            ->whereIn('market_code', $enabledMarkets)
            ->whereHas('league', fn ($q) => $q->where('enabled', true)->where('homepage_enabled', true))
            ->whereHas('fixture', fn ($q) => $q->whereIn('status', ['NS', 'TBD', 'PST']))
            ->with(['fixture', 'league'])
            ->orderByDesc('admin_featured')
            ->orderByDesc('featured')
            ->orderBy('featured_priority')
            ->orderByDesc('confidence')
            ->orderByDesc('probability')
            ->limit($limit)
            ->get();
    }
}
