<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * API-Football v3 — Full Service
 * Uses proper bet IDs per API docs:
 *   ID 1 = Match Winner, ID 5 = Goals Over/Under, ID 8 = Both Teams Score
 */
class ApiFootballServiceEnhanced
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://v3.football.api-sports.io';
        $this->apiKey = config('services.api_football.key');
    }

    protected function request(string $endpoint, array $params = [], bool $noCache = false): ?array
    {
        $cacheKey = 'af_' . md5($endpoint . json_encode($params));
        if (!$noCache && $cached = Cache::get($cacheKey)) return $cached;

        try {
            $response = Http::timeout(25)->withHeaders([
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                if (!$noCache) Cache::put($cacheKey, $data, now()->addMinutes(30));
                return $data;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('API-Football: ' . $e->getMessage());
            return null;
        }
    }

    // ═══ FIXTURES ═══════════════════════════════════════

    public function getFixturesByDate(string $date, ?int $league = null): ?array
    {
        $p = ['date' => $date];
        if ($league) $p['league'] = $league;
        return $this->request('/fixtures', $p);
    }

    /** Get one fixture by ID */
    public function getFixturesById(int $fixtureId): ?array
    {
        return $this->request('/fixtures', ['id' => $fixtureId]);
    }

    /** Get one fixture with events, lineups, stats, players all included */
    public function getFixtureFull(int $fixtureId): ?array
    {
        return $this->request('/fixtures', ['id' => $fixtureId]);
    }

    public function getLiveFixtures(): ?array
    {
        return $this->request('/fixtures', ['live' => 'all'], true);
    }

    public function getTeamLastFixtures(int $teamId, int $count = 10): ?array
    {
        return $this->request('/fixtures', ['team' => $teamId, 'last' => $count]);
    }

    // ═══ ODDS (using proper bet IDs) ════════════════════

    /**
     * Get odds for a fixture from a specific bookmaker
     * Bet IDs: 1=Match Winner, 5=Goals Over/Under, 8=Both Teams Score
     */
    public function getOdds(int $fixtureId, ?int $bookmaker = null): ?array
    {
        $p = ['fixture' => $fixtureId];
        if ($bookmaker) $p['bookmaker'] = $bookmaker;
        return $this->request('/odds', $p);
    }

    /** Get odds for a league+season from a specific bookmaker */
    public function getOddsByLeague(int $leagueId, int $season, int $bookmaker, int $bet): ?array
    {
        return $this->request('/odds', [
            'league' => $leagueId, 'season' => $season,
            'bookmaker' => $bookmaker, 'bet' => $bet,
        ]);
    }

    // ═══ PREDICTIONS (API AI) ═══════════════════════════

    public function getPredictions(int $fixtureId): ?array
    {
        return $this->request('/predictions', ['fixture' => $fixtureId]);
    }

    // ═══ HEAD TO HEAD ═══════════════════════════════════

    public function getHeadToHead(int $t1, int $t2, int $last = 10): ?array
    {
        return $this->request('/fixtures/headtohead', ['h2h' => "{$t1}-{$t2}", 'last' => $last]);
    }

    // ═══ STANDINGS ══════════════════════════════════════

    public function getStandings(int $leagueId, int $season): ?array
    {
        return $this->request('/standings', ['league' => $leagueId, 'season' => $season]);
    }

    // ═══ TEAMS ══════════════════════════════════════════

    public function getTeamStatistics(int $teamId, int $leagueId, int $season): ?array
    {
        return $this->request('/teams/statistics', ['team' => $teamId, 'league' => $leagueId, 'season' => $season]);
    }

    public function getTeamInfo(int $teamId): ?array
    {
        return $this->request('/teams', ['id' => $teamId]);
    }

    // ═══ PLAYERS ════════════════════════════════════════

    public function getPlayers(int $teamId, int $season): ?array
    {
        return $this->request('/players', ['team' => $teamId, 'season' => $season]);
    }

    // ═══ INJURIES ═══════════════════════════════════════

    public function getInjuriesByFixture(int $fixtureId): ?array
    {
        return $this->request('/injuries', ['fixture' => $fixtureId], true);
    }

    public function getInjuriesByTeam(int $teamId, int $season): ?array
    {
        return $this->request('/injuries', ['team' => $teamId, 'season' => $season], true);
    }

    public function getInjuriesByLeague(int $leagueId, int $season): ?array
    {
        return $this->request('/injuries', ['league' => $leagueId, 'season' => $season], true);
    }

    // ═══ FIXTURE DETAILS ════════════════════════════════

    public function getFixtureEvents(int $fixtureId): ?array
    {
        return $this->request('/fixtures/events', ['fixture' => $fixtureId]);
    }

    public function getFixtureLineups(int $fixtureId): ?array
    {
        return $this->request('/fixtures/lineups', ['fixture' => $fixtureId]);
    }

    public function getFixturePlayers(int $fixtureId): ?array
    {
        return $this->request('/fixtures/players', ['fixture' => $fixtureId]);
    }

    public function getFixtureStats(int $fixtureId): ?array
    {
        return $this->request('/fixtures/statistics', ['fixture' => $fixtureId]);
    }

    // ═══ STATUS ═════════════════════════════════════════

    public function getStatus(): ?array
    {
        return $this->request('/status', [], true);
    }

    public function getRemainingRequests(): int
    {
        $s = $this->getStatus();
        return $s['response']['requests']['limit_day'] - ($s['response']['requests']['current'] ?? 0) ?? 0;
    }
}
