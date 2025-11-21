<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\ApiFootballService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    protected $apiFootballService;

    public function __construct(ApiFootballService $apiFootballService)
    {
        $this->apiFootballService = $apiFootballService;
    }

    /**
     * Display league standings
     */
    public function standings(Request $request)
    {
        $selectedLeague = $request->get('league', '39'); // Default to Premier League
        $selectedSeason = $request->get('season', date('Y'));
        
        // Get standings from API
        $standings = $this->apiFootballService->getStandings($selectedLeague, $selectedSeason);
        
        // If API fails, provide mock data for development
        if (!$standings) {
            $standings = $this->getMockStandings($selectedLeague, $selectedSeason);
        }
        
        // Get available leagues for dropdown
        $leagues = $this->getAvailableLeagues();
        
        return view('match.standings', compact('standings', 'leagues', 'selectedLeague', 'selectedSeason'));
    }

    /**
     * Display upcoming matches
     */
    public function upcoming(Request $request)
    {
        $selectedLeague = $request->get('league', '39'); // Default to Premier League
        $selectedDate = $request->get('date', date('Y-m-d'));
        
        // Handle quick date navigation
        if ($request->get('action') === 'today') {
            $selectedDate = date('Y-m-d');
        } elseif ($request->get('action') === 'tomorrow') {
            $selectedDate = date('Y-m-d', strtotime('+1 day'));
        }
        
        // Get upcoming matches from API
        $matches = $this->apiFootballService->getUpcomingMatches($selectedLeague, $selectedDate);
        
        // If API fails, provide mock data for development
        if (!$matches) {
            $matches = $this->getMockUpcomingMatches($selectedLeague, $selectedDate);
        }
        
        // Get available leagues for dropdown
        $leagues = $this->getAvailableLeagues();
        
        return view('match.upcoming', compact('matches', 'leagues', 'selectedLeague', 'selectedDate'));
    }

    /**
     * Get available leagues for dropdown
     */
    private function getAvailableLeagues()
    {
        return [
            '39' => 'Premier League',
            '140' => 'La Liga',
            '78' => 'Bundesliga',
            '135' => 'Serie A',
            '61' => 'Ligue 1',
            '88' => 'Eredivisie',
            '94' => 'Primeira Liga',
            '203' => 'Süper Lig',
            '169' => 'Championship',
            '71' => 'Serie A Brazil',
            '253' => 'MLS',
            '262' => 'Liga MX',
            '307' => 'Saudi Pro League',
            '319' => 'UAE Pro League',
            '322' => 'Qatar Stars League',
            '333' => 'Egyptian Premier League',
            '350' => 'South African Premier Division',
            '357' => 'Nigerian Professional Football League',
            '364' => 'Ghana Premier League',
            '371' => 'Kenya Premier League',
        ];
    }

    /**
     * Get mock standings data for development
     */
    private function getMockStandings($leagueId, $season)
    {
        $leagueNames = [
            '39' => 'Premier League',
            '140' => 'La Liga',
            '78' => 'Bundesliga',
            '135' => 'Serie A',
            '61' => 'Ligue 1',
        ];

        $leagueName = $leagueNames[$leagueId] ?? 'Premier League';

        return [
            'response' => [
                [
                    'league' => [
                        'id' => $leagueId,
                        'name' => $leagueName,
                        'country' => 'England',
                        'logo' => 'https://media.api-sports.io/football/leagues/39.png',
                        'flag' => 'https://media.api-sports.io/flags/gb.svg',
                        'season' => $season,
                        'standings' => [
                            [
                                [
                                    'rank' => 1,
                                    'team' => ['id' => 50, 'name' => 'Manchester City', 'logo' => 'https://media.api-sports.io/football/teams/50.png'],
                                    'points' => 89,
                                    'goalsDiff' => 61,
                                    'group' => 'Premier League',
                                    'form' => 'WWWDW',
                                    'status' => 'same',
                                    'description' => 'Promotion - Champions League',
                                    'all' => ['played' => 38, 'win' => 28, 'draw' => 5, 'lose' => 5, 'goals' => ['for' => 94, 'against' => 33]],
                                    'home' => ['played' => 19, 'win' => 15, 'draw' => 2, 'lose' => 2, 'goals' => ['for' => 49, 'against' => 17]],
                                    'away' => ['played' => 19, 'win' => 13, 'draw' => 3, 'lose' => 3, 'goals' => ['for' => 45, 'against' => 16]],
                                    'update' => '2023-05-28T00:00:00+00:00'
                                ],
                                [
                                    'rank' => 2,
                                    'team' => ['id' => 42, 'name' => 'Arsenal', 'logo' => 'https://media.api-sports.io/football/teams/42.png'],
                                    'points' => 84,
                                    'goalsDiff' => 45,
                                    'group' => 'Premier League',
                                    'form' => 'WWLWW',
                                    'status' => 'same',
                                    'description' => 'Promotion - Champions League',
                                    'all' => ['played' => 38, 'win' => 26, 'draw' => 6, 'lose' => 6, 'goals' => ['for' => 88, 'against' => 43]],
                                    'home' => ['played' => 19, 'win' => 14, 'draw' => 3, 'lose' => 2, 'goals' => ['for' => 45, 'against' => 20]],
                                    'away' => ['played' => 19, 'win' => 12, 'draw' => 3, 'lose' => 4, 'goals' => ['for' => 43, 'against' => 23]],
                                    'update' => '2023-05-28T00:00:00+00:00'
                                ],
                                [
                                    'rank' => 3,
                                    'team' => ['id' => 33, 'name' => 'Manchester United', 'logo' => 'https://media.api-sports.io/football/teams/33.png'],
                                    'points' => 75,
                                    'goalsDiff' => 15,
                                    'group' => 'Premier League',
                                    'form' => 'WLDWW',
                                    'status' => 'same',
                                    'description' => 'Promotion - Champions League',
                                    'all' => ['played' => 38, 'win' => 23, 'draw' => 6, 'lose' => 9, 'goals' => ['for' => 58, 'against' => 43]],
                                    'home' => ['played' => 19, 'win' => 15, 'draw' => 2, 'lose' => 2, 'goals' => ['for' => 36, 'against' => 18]],
                                    'away' => ['played' => 19, 'win' => 8, 'draw' => 4, 'lose' => 7, 'goals' => ['for' => 22, 'against' => 25]],
                                    'update' => '2023-05-28T00:00:00+00:00'
                                ],
                                [
                                    'rank' => 4,
                                    'team' => ['id' => 40, 'name' => 'Liverpool', 'logo' => 'https://media.api-sports.io/football/teams/40.png'],
                                    'points' => 67,
                                    'goalsDiff' => 28,
                                    'group' => 'Premier League',
                                    'form' => 'WWWWW',
                                    'status' => 'same',
                                    'description' => 'Promotion - Champions League',
                                    'all' => ['played' => 38, 'win' => 19, 'draw' => 10, 'lose' => 9, 'goals' => ['for' => 75, 'against' => 47]],
                                    'home' => ['played' => 19, 'win' => 13, 'draw' => 4, 'lose' => 2, 'goals' => ['for' => 42, 'against' => 18]],
                                    'away' => ['played' => 19, 'win' => 6, 'draw' => 6, 'lose' => 7, 'goals' => ['for' => 33, 'against' => 29]],
                                    'update' => '2023-05-28T00:00:00+00:00'
                                ],
                                [
                                    'rank' => 5,
                                    'team' => ['id' => 49, 'name' => 'Newcastle United', 'logo' => 'https://media.api-sports.io/football/teams/49.png'],
                                    'points' => 71,
                                    'goalsDiff' => 35,
                                    'group' => 'Premier League',
                                    'form' => 'WWDWW',
                                    'status' => 'same',
                                    'description' => 'Promotion - Europa League',
                                    'all' => ['played' => 38, 'win' => 19, 'draw' => 14, 'lose' => 5, 'goals' => ['for' => 68, 'against' => 33]],
                                    'home' => ['played' => 19, 'win' => 12, 'draw' => 6, 'lose' => 1, 'goals' => ['for' => 35, 'against' => 12]],
                                    'away' => ['played' => 19, 'win' => 7, 'draw' => 8, 'lose' => 4, 'goals' => ['for' => 33, 'against' => 21]],
                                    'update' => '2023-05-28T00:00:00+00:00'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get mock upcoming matches data for development
     */
    private function getMockUpcomingMatches($leagueId, $date)
    {
        $leagueNames = [
            '39' => 'Premier League',
            '140' => 'La Liga',
            '78' => 'Bundesliga',
            '135' => 'Serie A',
            '61' => 'Ligue 1',
        ];

        $leagueName = $leagueNames[$leagueId] ?? 'Premier League';

        return [
            'response' => [
                [
                    'fixture' => [
                        'id' => 1035120,
                        'referee' => 'M. Oliver',
                        'timezone' => 'UTC',
                        'date' => $date . 'T15:00:00+00:00',
                        'timestamp' => strtotime($date . ' 15:00:00'),
                        'periods' => ['first' => null, 'second' => null],
                        'venue' => ['id' => 5, 'name' => 'Old Trafford', 'city' => 'Manchester'],
                        'status' => ['long' => 'Not Started', 'short' => 'NS', 'elapsed' => null]
                    ],
                    'league' => [
                        'id' => $leagueId,
                        'name' => $leagueName,
                        'country' => 'England',
                        'logo' => 'https://media.api-sports.io/football/leagues/39.png',
                        'flag' => 'https://media.api-sports.io/flags/gb.svg',
                        'season' => date('Y'),
                        'round' => 'Regular Season - 1'
                    ],
                    'teams' => [
                        'home' => ['id' => 33, 'name' => 'Manchester United', 'logo' => 'https://media.api-sports.io/football/teams/33.png', 'winner' => null],
                        'away' => ['id' => 40, 'name' => 'Liverpool', 'logo' => 'https://media.api-sports.io/football/teams/40.png', 'winner' => null]
                    ],
                    'goals' => ['home' => null, 'away' => null],
                    'score' => [
                        'halftime' => ['home' => null, 'away' => null],
                        'fulltime' => ['home' => null, 'away' => null],
                        'extratime' => ['home' => null, 'away' => null],
                        'penalty' => ['home' => null, 'away' => null]
                    ]
                ],
                [
                    'fixture' => [
                        'id' => 1035121,
                        'referee' => 'A. Taylor',
                        'timezone' => 'UTC',
                        'date' => $date . 'T17:30:00+00:00',
                        'timestamp' => strtotime($date . ' 17:30:00'),
                        'periods' => ['first' => null, 'second' => null],
                        'venue' => ['id' => 6, 'name' => 'Emirates Stadium', 'city' => 'London'],
                        'status' => ['long' => 'Not Started', 'short' => 'NS', 'elapsed' => null]
                    ],
                    'league' => [
                        'id' => $leagueId,
                        'name' => $leagueName,
                        'country' => 'England',
                        'logo' => 'https://media.api-sports.io/football/leagues/39.png',
                        'flag' => 'https://media.api-sports.io/flags/gb.svg',
                        'season' => date('Y'),
                        'round' => 'Regular Season - 1'
                    ],
                    'teams' => [
                        'home' => ['id' => 42, 'name' => 'Arsenal', 'logo' => 'https://media.api-sports.io/football/teams/42.png', 'winner' => null],
                        'away' => ['id' => 50, 'name' => 'Manchester City', 'logo' => 'https://media.api-sports.io/football/teams/50.png', 'winner' => null]
                    ],
                    'goals' => ['home' => null, 'away' => null],
                    'score' => [
                        'halftime' => ['home' => null, 'away' => null],
                        'fulltime' => ['home' => null, 'away' => null],
                        'extratime' => ['home' => null, 'away' => null],
                        'penalty' => ['home' => null, 'away' => null]
                    ]
                ]
            ]
        ];
    }
}
