<?php

namespace App\Services\Prediction;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Public read layer for the prediction engine. Controllers stay thin and
 * never calculate probabilities/eligibility themselves.
 */
class PublicPredictionService
{
    /**
     * Codes of markets that are currently enabled.
     *
     * @return array<int,string>
     */
    public function enabledMarketCodes(): array
    {
        return PredictionCategory::query()
            ->where('enabled', true)
            ->pluck('code')
            ->all();
    }

    /**
     * Upcoming fixtures that have at least one published, eligible prediction.
     *
     * @return LengthAwarePaginator<int,Fixture>
     */
    public function publishedFixtures(?int $leagueApiId = null, ?string $marketCode = null, ?string $dateRange = null, int $perPage = 20): LengthAwarePaginator
    {
        $enabledMarkets = $this->enabledMarketCodes();

        $predictionFilters = function ($query) use ($leagueApiId, $marketCode, $enabledMarkets) {
            $query->where('status', 'published')
                ->whereIn('market_code', $enabledMarkets);

            if ($leagueApiId) {
                $query->where('league_id', $leagueApiId);
            }

            if ($marketCode) {
                $query->where('market_code', $marketCode);
            }
        };

        $query = Fixture::query()
            ->whereIn('status', ['NS', 'TBD', 'PST'])
            ->whereHas('league', fn ($q) => $q->where('enabled', true))
            ->whereHas('predictions', $predictionFilters)
            ->with([
                'league',
                'predictions' => function ($query) use ($predictionFilters) {
                    $predictionFilters($query);
                    $query->orderByDesc('featured')
                        ->orderBy('featured_priority')
                        ->orderByDesc('confidence');
                },
            ])
            ->orderBy('match_date');

        $this->applyDateRange($query, $dateRange);

        return $query->paginate($perPage);
    }

    public function getLeaguePredictions(League $league, ?string $marketCode = null, ?string $dateRange = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->publishedFixtures($league->api_football_league_id, $marketCode, $dateRange, $perPage);
    }

    public function getMarketPredictions(string $marketCode, int $perPage = 20): LengthAwarePaginator
    {
        return $this->publishedFixtures(null, $marketCode, null, $perPage);
    }

    /**
     * All published predictions for a single fixture (already bound by league).
     */
    public function getFixturePredictions(Fixture $fixture): Fixture
    {
        $fixture->load([
            'league',
            'features',
            'predictions' => function ($query) {
                $query->where('status', 'published')
                    ->whereIn('market_code', $this->enabledMarketCodes())
                    ->orderByDesc('featured')
                    ->orderBy('featured_priority')
                    ->orderByDesc('confidence');
            },
        ]);

        return $fixture;
    }

    /**
     * Leagues eligible for public navigation (enabled + homepage visible).
     */
    public function getPublicLeagues(): Collection
    {
        return League::query()
            ->where('enabled', true)
            ->where('homepage_enabled', true)
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    protected function applyDateRange($query, ?string $dateRange): void
    {
        switch ($dateRange) {
            case 'today':
                $query->whereDate('match_date', today());
                break;
            case 'tomorrow':
                $query->whereDate('match_date', today()->addDay());
                break;
            case '3days':
                $query->whereBetween('match_date', [today()->startOfDay(), today()->addDays(3)->endOfDay()]);
                break;
            case '7days':
                $query->whereBetween('match_date', [today()->startOfDay(), today()->addDays(7)->endOfDay()]);
                break;
        }
    }
}
