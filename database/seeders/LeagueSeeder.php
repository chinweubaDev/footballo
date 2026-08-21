<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    public function run(): void
    {
        $season = (int) config('prediction.default_season', 2025);

        $leagues = [
            ['name' => 'Premier League', 'api_football_league_id' => 39, 'slug' => 'premier-league', 'country' => 'England'],
            ['name' => 'La Liga', 'api_football_league_id' => 140, 'slug' => 'la-liga', 'country' => 'Spain'],
            ['name' => 'Serie A', 'api_football_league_id' => 135, 'slug' => 'serie-a', 'country' => 'Italy'],
            ['name' => 'Bundesliga', 'api_football_league_id' => 78, 'slug' => 'bundesliga', 'country' => 'Germany'],
            ['name' => 'Ligue 1', 'api_football_league_id' => 61, 'slug' => 'ligue-1', 'country' => 'France'],
            ['name' => 'Eredivisie', 'api_football_league_id' => 88, 'slug' => 'eredivisie', 'country' => 'Netherlands'],
        ];

        foreach ($leagues as $index => $league) {
            League::updateOrCreate(
                ['api_football_league_id' => $league['api_football_league_id']],
                array_merge($league, [
                    'logo' => null,
                    'season' => $season,
                    'enabled' => true,
                    'prediction_enabled' => true,
                    'homepage_enabled' => true,
                    'priority' => $index + 1,
                    'prediction_min_confidence' => 75,
                    'auto_publish' => true,
                ])
            );
        }
    }
}
