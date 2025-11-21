<?php

namespace Database\Seeders;

use App\Models\Tip;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tips = [
            // VIP Tips
            [
                'title' => 'High Confidence Home Win',
                'content' => 'Manchester United has been in excellent form at home this season, winning 8 of their last 10 matches at Old Trafford. Liverpool, while strong, has struggled in away games against top-6 opponents. Our analysis shows a clear advantage for the home team.',
                'type' => 'vip',
                'league_name' => 'Premier League',
                'home_team' => 'Manchester United',
                'away_team' => 'Liverpool',
                'match_date' => now()->addDays(2),
                'match_time' => '15:30',
                'prediction' => 'Home Win',
                'odds' => 2.10,
                'status' => 'pending',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Over 2.5 Goals - Strong Pick',
                'content' => 'Both teams have been scoring consistently, with an average of 3.2 goals per game in their last 5 meetings. Barcelona\'s attacking form combined with Real Madrid\'s defensive vulnerabilities makes this a solid over 2.5 goals bet.',
                'type' => 'vip',
                'league_name' => 'La Liga',
                'home_team' => 'Barcelona',
                'away_team' => 'Real Madrid',
                'match_date' => now()->addDays(3),
                'match_time' => '20:00',
                'prediction' => 'Over 2.5 Goals',
                'odds' => 1.85,
                'status' => 'pending',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Both Teams to Score',
                'content' => 'Juventus and AC Milan both have strong attacking records this season. With key players returning from injury, we expect both teams to find the back of the net in this crucial Serie A clash.',
                'type' => 'vip',
                'league_name' => 'Serie A',
                'home_team' => 'Juventus',
                'away_team' => 'AC Milan',
                'match_date' => now()->addDays(1),
                'match_time' => '18:00',
                'prediction' => 'Both Teams to Score',
                'odds' => 1.75,
                'status' => 'pending',
                'is_featured' => false,
                'is_active' => true,
            ],

            // VVIP Tips
            [
                'title' => 'Exact Score Prediction',
                'content' => 'Our exclusive analysis reveals a 2-1 victory for Manchester City. Based on recent form, head-to-head statistics, and key player availability, we\'re confident in this exact score prediction. City\'s home advantage and Arsenal\'s defensive issues make this our top VVIP pick.',
                'type' => 'vvip',
                'league_name' => 'Premier League',
                'home_team' => 'Manchester City',
                'away_team' => 'Arsenal',
                'match_date' => now()->addDays(2),
                'match_time' => '17:30',
                'prediction' => 'Exact Score: 2-1',
                'odds' => 8.50,
                'status' => 'pending',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'title' => 'First Half Winner',
                'content' => 'PSG has been exceptional in first halves this season, leading at half-time in 12 of their last 15 matches. Bayern Munich tends to start slowly in away games. Our exclusive insight shows PSG to lead at half-time.',
                'type' => 'vvip',
                'league_name' => 'Champions League',
                'home_team' => 'PSG',
                'away_team' => 'Bayern Munich',
                'match_date' => now()->addDays(4),
                'match_time' => '21:00',
                'prediction' => 'First Half Winner: PSG',
                'odds' => 3.20,
                'status' => 'pending',
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Player to Score',
                'content' => 'Lautaro Martinez has scored in 6 of his last 8 matches against Napoli. His exceptional form and Napoli\'s defensive vulnerabilities make him our top pick to score in this crucial Serie A encounter.',
                'type' => 'vvip',
                'league_name' => 'Serie A',
                'home_team' => 'Inter Milan',
                'away_team' => 'Napoli',
                'match_date' => now()->addDays(3),
                'match_time' => '19:45',
                'prediction' => 'Lautaro Martinez to Score',
                'odds' => 2.80,
                'status' => 'pending',
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        foreach ($tips as $tip) {
            Tip::create($tip);
        }
    }
}
