<?php

namespace App\Services\Prediction\Admin;

use App\Models\League;
use App\Models\LeagueMarketGate;
use App\Models\PredictionCategory;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Phase 1I — league x market gate overrides.
 *
 * Reads and writes the most-specific gate tier in the precedence chain:
 *   league + market  >  market  >  league  >  global.
 */
class LeagueMarketGateService
{
    /**
     * @return Collection<int,League>
     */
    public function leagues(): Collection
    {
        return League::query()->orderBy('priority')->orderBy('name')->get();
    }

    /**
     * @return Collection<int,PredictionCategory>
     */
    public function markets(): Collection
    {
        return PredictionCategory::query()->orderBy('sort_order')->get();
    }

    /**
     * @return array<int, array<string, LeagueMarketGate|null>>
     */
    public function matrix(): array
    {
        $leagues = $this->leagues();
        $gates = LeagueMarketGate::query()
            ->whereIn('league_id', $leagues->pluck('api_football_league_id')->all())
            ->get();

        $matrix = [];

        foreach ($leagues as $league) {
            $matrix[$league->api_football_league_id] = [];

            foreach ($this->markets() as $market) {
                $matrix[$league->api_football_league_id][$market->code] = $gates
                    ->first(fn ($g) => $g->league_id == $league->api_football_league_id && $g->market_code === $market->code);
            }
        }

        return $matrix;
    }

    public function update(
        League $league,
        string $marketCode,
        bool $enabled,
        ?int $minProbability,
        ?int $minConfidence,
        User $admin,
    ): LeagueMarketGate {
        $gate = LeagueMarketGate::updateOrCreate(
            [
                'league_id' => $league->api_football_league_id,
                'market_code' => $marketCode,
            ],
            [
                'enabled' => $enabled,
                'min_probability' => $minProbability,
                'min_confidence' => $minConfidence,
            ],
        );

        return $gate;
    }
}
