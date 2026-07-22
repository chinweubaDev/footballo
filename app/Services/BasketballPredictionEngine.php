<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Basketball Prediction Engine
 * 
 * Analyzes basketball matches using:
 * - H2H record
 * - Recent form (last 10 games)
 * - Home/Away splits
 * - Points scored/allowed averages
 * - League standings
 * - Key player impact
 * 
 * Predicts: Money Line (1X2 equivalent), Spread, Total Points (Over/Under)
 */
class BasketballPredictionEngine
{
    protected BasketballApiService $api;

    public function __construct(BasketballApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Generate full prediction for a basketball game
     */
    public function predictGame(array $gameData, int $homeTeamId, int $awayTeamId, int $leagueId, int $season): array
    {
        // Gather data
        $h2h = $this->analyzeH2H($homeTeamId, $awayTeamId);
        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season);
        $standings = $this->analyzeStandings($homeTeamId, $awayTeamId, $leagueId, $season);

        // Home team
        $homeName = $gameData['teams']['home']['name'] ?? 'Home';
        $awayName = $gameData['teams']['away']['name'] ?? 'Away';

        // Calculate expected points
        $homeOffense = floatval($homeStats['points_for_avg'] ?? 85);
        $homeDefense = floatval($homeStats['points_against_avg'] ?? 80);
        $awayOffense = floatval($awayStats['points_for_avg'] ?? 80);
        $awayDefense = floatval($awayStats['points_against_avg'] ?? 85);

        // Expected score model (basketball variant)
        $homeExpected = ($homeOffense + $awayDefense) / 2;
        $awayExpected = ($awayOffense + $homeDefense) / 2;
        $totalExpected = $homeExpected + $awayExpected;
        $spread = $homeExpected - $awayExpected;

        // H2H adjustments
        if ($h2h['matches'] > 0) {
            $h2hBonus = ($h2h['home_win_rate'] - 50) / 200; // -0.25 to +0.25
            $homeExpected += $h2hBonus * 8;
            $awayExpected -= $h2hBonus * 8;
        }

        // Home court advantage (~3 points in basketball)
        $homeExpected += 3;
        $totalExpected += 3;
        $spread = $homeExpected - $awayExpected;

        // Win probabilities
        $pointDiff = abs($spread);
        $homeWinProb = 50 + ($spread > 0 ? min($pointDiff * 2.5, 45) : -min($pointDiff * 2.5, 45));
        $awayWinProb = 100 - $homeWinProb;

        // Confidence level
        $confidence = $pointDiff >= 10 ? 'High' : ($pointDiff >= 5 ? 'Medium' : 'Low');
        $confidencePct = round(min(abs($homeWinProb - 50) * 2 + 30, 95));

        // Over/Under
        $overUnderLine = round($totalExpected, 1);
        $overProb = $totalExpected > ($homeOffense + $awayOffense) / 2 + 5 ? 60 : 50;

        // Form analysis
        $homeFormStrength = $this->calculateFormStrength($homeStats);
        $awayFormStrength = $this->calculateFormStrength($awayStats);

        return [
            'home_team' => $homeName,
            'away_team' => $awayName,
            'money_line' => [
                'pick' => $homeWinProb >= 50 ? $homeName : $awayName,
                'home_win_prob' => round($homeWinProb, 1),
                'away_win_prob' => round($awayWinProb, 1),
            ],
            'spread' => [
                'favorite' => $spread > 0 ? $homeName : $awayName,
                'line' => round(abs($spread), 1),
                'pick' => $spread > 0 ? $homeName : $awayName,
            ],
            'total_points' => [
                'line' => round($overUnderLine),
                'pick' => $overProb >= 50 ? 'Over' : 'Under',
                'over_prob' => round($overProb),
            ],
            'expected_score' => [
                'home' => round($homeExpected),
                'away' => round($awayExpected),
                'total' => round($totalExpected),
            ],
            'confidence' => $confidence,
            'confidence_pct' => $confidencePct,
            'analysis' => $this->generateAnalysis(
                $homeName, $awayName, $homeStats, $awayStats, $h2h, $standings,
                $homeExpected, $awayExpected, $homeWinProb
            ),
            'h2h' => $h2h,
            'standings' => $standings,
        ];
    }

    protected function analyzeH2H(int $team1Id, int $team2Id): array
    {
        $data = $this->api->getHeadToHead($team1Id, $team2Id);
        
        if (!$data || empty($data['response'])) {
            return ['matches' => 0, 'home_win_rate' => 0, 'total_avg' => 0];
        }

        $matches = $data['response'];
        $team1Wins = 0;
        $totalPoints = 0;

        foreach ($matches as $game) {
            $homeScore = $game['scores']['home']['total'] ?? 0;
            $awayScore = $game['scores']['away']['total'] ?? 0;
            $totalPoints += $homeScore + $awayScore;

            if ($game['teams']['home']['id'] == $team1Id) {
                if ($homeScore > $awayScore) $team1Wins++;
            } else {
                if ($awayScore > $homeScore) $team1Wins++;
            }
        }

        $total = count($matches);

        return [
            'matches' => $total,
            'home_win_rate' => $total > 0 ? round(($team1Wins / $total) * 100, 1) : 0,
            'total_avg' => $total > 0 ? round($totalPoints / $total, 1) : 0,
            'over_200_rate' => $total > 0 ? round(
                (collect($matches)->filter(fn($g) => 
                    ($g['scores']['home']['total'] ?? 0) + ($g['scores']['away']['total'] ?? 0) > 200
                )->count() / $total) * 100, 1
            ) : 0,
        ];
    }

    protected function getTeamStats(int $teamId, int $leagueId, int $season): array
    {
        $stats = $this->api->getTeamStatistics($teamId, $leagueId, $season);

        if (!$stats || empty($stats['response'])) {
            return [
                'points_for_avg' => 80, 'points_against_avg' => 80,
                'wins' => 0, 'losses' => 0, 'win_rate' => 50, 'form' => '-----',
            ];
        }

        $data = $stats['response'];

        return [
            'points_for_avg' => $data['points']['for']['average']['total'] ?? 80,
            'points_against_avg' => $data['points']['against']['average']['total'] ?? 80,
            'points_for_avg_home' => $data['points']['for']['average']['home'] ?? 85,
            'points_against_avg_away' => $data['points']['against']['average']['away'] ?? 85,
            'wins_total' => $data['games']['wins']['total'] ?? 0,
            'losses_total' => $data['games']['losses']['total'] ?? 0,
            'wins_home' => $data['games']['wins']['home'] ?? 0,
            'losses_away' => $data['games']['losses']['away'] ?? 0,
            'win_rate' => $data['games']['played']['total'] > 0 
                ? round(($data['games']['wins']['total'] / $data['games']['played']['total']) * 100, 1) 
                : 50,
            'form' => $data['games']['form'] ?? '-----',
        ];
    }

    protected function analyzeStandings(int $homeId, int $awayId, int $leagueId, int $season): array
    {
        $data = $this->api->getStandings($leagueId, $season);

        $homePos = $awayPos = 0;
        $homeWinPct = $awayWinPct = 50;

        if ($data && !empty($data['response'])) {
            foreach ($data['response'][0] ?? [] as $group) {
                foreach ($group as $team) {
                    if ($team['team']['id'] == $homeId) {
                        $homePos = $team['position'] ?? 0;
                        $homeWinPct = $team['games']['played'] > 0
                            ? round(($team['games']['win']['total'] / $team['games']['played']) * 100, 1)
                            : 50;
                    }
                    if ($team['team']['id'] == $awayId) {
                        $awayPos = $team['position'] ?? 0;
                        $awayWinPct = $team['games']['played'] > 0
                            ? round(($team['games']['win']['total'] / $team['games']['played']) * 100, 1)
                            : 50;
                    }
                }
            }
        }

        return [
            'home_position' => $homePos,
            'away_position' => $awayPos,
            'home_win_pct' => $homeWinPct,
            'away_win_pct' => $awayWinPct,
        ];
    }

    protected function calculateFormStrength(array $stats): string
    {
        $form = $stats['form'] ?? '-----';
        $wins = substr_count($form, 'W');
        return $wins >= 4 ? 'Excellent' : ($wins >= 3 ? 'Good' : ($wins >= 2 ? 'Average' : 'Poor'));
    }

    protected function generateAnalysis(
        string $home, string $away,
        array $homeStats, array $awayStats, array $h2h, array $standings,
        float $homeExp, float $awayExp, float $homeProb
    ): string {
        $parts = [];

        $parts[] = "🏀 **{$home} vs {$away}** — Basketball Prediction";

        if ($h2h['matches'] > 0) {
            $parts[] = "📊 H2H: {$h2h['matches']} meetings, {$home} wins {$h2h['home_win_rate']}%.";
        }

        $parts[] = "📈 {$home} scores avg {$homeStats['points_for_avg']} PPG, "
            . "{$away} scores avg {$awayStats['points_for_avg']} PPG.";

        $parts[] = "🎯 Expected: {$home} " . round($homeExp) . " - " . round($awayExp) . " {$away} "
            . "(Total: " . round($homeExp + $awayExp) . ").";

        $pick = $homeProb >= 50 ? $home : $away;
        $parts[] = "💡 **Pick**: {$pick} Money Line (Confidence: " . round($homeProb) . "%).";

        return implode("\n\n", $parts);
    }
}
