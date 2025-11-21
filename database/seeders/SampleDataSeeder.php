<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Prediction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample fixtures
        $fixtures = [
            [
                'api_fixture_id' => 1001,
                'league_name' => 'Premier League',
                'league_country' => 'England',
                'league_id' => 39,
                'season' => 2024,
                'home_team' => 'Manchester United',
                'away_team' => 'Liverpool',
                'home_team_id' => 33,
                'away_team_id' => 40,
                'match_date' => now()->addHours(2),
                'today_tip' => true,
                'featured' => true,
            ],
            [
                'api_fixture_id' => 1002,
                'league_name' => 'La Liga',
                'league_country' => 'Spain',
                'league_id' => 140,
                'season' => 2024,
                'home_team' => 'Barcelona',
                'away_team' => 'Real Madrid',
                'home_team_id' => 529,
                'away_team_id' => 541,
                'match_date' => now()->addHours(4),
                'featured' => true,
                'maxodds_tip' => true,
            ],
            [
                'api_fixture_id' => 1003,
                'league_name' => 'Bundesliga',
                'league_country' => 'Germany',
                'league_id' => 78,
                'season' => 2024,
                'home_team' => 'Bayern Munich',
                'away_team' => 'Borussia Dortmund',
                'home_team_id' => 157,
                'away_team_id' => 165,
                'match_date' => now()->addDays(1),
                'today_tip' => true,
            ],
        ];

        foreach ($fixtures as $fixtureData) {
            $fixture = Fixture::create($fixtureData);

            // Create predictions for each fixture
            $predictions = [
                [
                    'category' => '1X2',
                    'tip' => '1',
                    'confidence' => 75,
                    'odds' => 2.10,
                    'analysis' => 'Home team has strong form and advantage.',
                    'is_premium' => (bool) $fixture->featured,
                    'is_maxodds' => (bool) $fixture->maxodds_tip,
                ],
                [
                    'category' => 'Over/Under',
                    'tip' => 'Over 2.5',
                    'confidence' => 80,
                    'odds' => 1.85,
                    'analysis' => 'Both teams have strong attacking records.',
                    'is_premium' => false,
                    'is_maxodds' => false,
                ],
            ];

            foreach ($predictions as $predictionData) {
                Prediction::create(array_merge($predictionData, ['fixture_id' => $fixture->id]));
            }
        }
    }
}
