<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Basketball API Service using API-Sports Basketball endpoint
 * Uses the same API key as football (api-sports.io supports multiple sports)
 */
class BasketballApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $cacheMinutes;

    public function __construct()
    {
        // API-Sports basketball endpoint
        $this->baseUrl = 'https://v1.basketball.api-sports.io';
        $this->apiKey = config('services.api_football.key');
        $this->cacheMinutes = 30;
    }

    protected function request(string $endpoint, array $params = []): ?array
    {
        $cacheKey = 'basketball_api_' . md5($endpoint . json_encode($params));

        $cached = Cache::get($cacheKey);
        if ($cached) return $cached;

        try {
            $response = Http::timeout(30)->withHeaders([
                'x-rapidapi-host' => 'v1.basketball.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, now()->addMinutes($this->cacheMinutes));
                return $data;
            }

            Log::error('Basketball API: Request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Basketball API: Exception', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // ─── LEAGUES ───────────────────────────────────────────

    public function getLeagues(): ?array
    {
        return $this->request('/leagues');
    }

    public function getLeagueById(int $leagueId): ?array
    {
        return $this->request('/leagues', ['id' => $leagueId]);
    }

    // ─── TEAMS ─────────────────────────────────────────────

    public function getTeams(int $leagueId, int $season): ?array
    {
        return $this->request('/teams', ['league' => $leagueId, 'season' => $season]);
    }

    public function getTeamStatistics(int $teamId, int $leagueId, int $season): ?array
    {
        return $this->request('/teams/statistics', [
            'team' => $teamId,
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    // ─── GAMES ─────────────────────────────────────────────

    /**
     * Get games by date
     */
    public function getGamesByDate(string $date, ?int $leagueId = null): ?array
    {
        $params = ['date' => $date];
        if ($leagueId) $params['league'] = $leagueId;
        return $this->request('/games', $params);
    }

    /**
     * Get games by league + season
     */
    public function getGamesByLeague(int $leagueId, int $season): ?array
    {
        return $this->request('/games', [
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    /**
     * Get a specific game
     */
    public function getGameById(int $gameId): ?array
    {
        return $this->request('/games', ['id' => $gameId]);
    }

    /**
     * Get LIVE games
     */
    public function getLiveGames(): ?array
    {
        return $this->request('/games', ['live' => 'all']);
    }

    // ─── H2H ───────────────────────────────────────────────

    public function getHeadToHead(int $team1Id, int $team2Id): ?array
    {
        return $this->request('/games/h2h', [
            'h2h' => "{$team1Id}-{$team2Id}",
        ]);
    }

    // ─── STANDINGS ─────────────────────────────────────────

    public function getStandings(int $leagueId, int $season): ?array
    {
        return $this->request('/standings', [
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    // ─── PLAYERS ───────────────────────────────────────────

    public function getPlayers(int $teamId, int $season): ?array
    {
        return $this->request('/players', [
            'team' => $teamId,
            'season' => $season,
        ]);
    }
}
