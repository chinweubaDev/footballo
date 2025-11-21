<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiFootballService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.api_football.base_url');
        $this->apiKey = config('services.api_football.key');
    }

    /**
     * Get countries
     */
    public function getCountries()
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/countries');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch countries', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching countries', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get leagues by country
     */
    public function getLeaguesByCountry($country)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/leagues', [
                'country' => $country
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch leagues', [
                'country' => $country,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching leagues', [
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get fixtures by league and date range
     */
    public function getFixtures($leagueId, $season, $date)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/fixtures', [
                'league' => $leagueId,
                'season' => $season,
                'date' => $date,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch fixtures', [
                'league_id' => $leagueId,
                'season' => $season,
                'date' => $date,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching fixtures', [
                'league_id' => $leagueId,
                'season' => $season,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get fixture by ID
     */
    public function getFixture($fixtureId)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/fixtures', [
                'id' => $fixtureId
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch fixture', [
                'fixture_id' => $fixtureId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching fixture', [
                'fixture_id' => $fixtureId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get team statistics
     */
    public function getTeamStats($teamId, $leagueId, $season)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/teams/statistics', [
                'team' => $teamId,
                'league' => $leagueId,
                'season' => $season
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch team stats', [
                'team_id' => $teamId,
                'league_id' => $leagueId,
                'season' => $season,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching team stats', [
                'team_id' => $teamId,
                'league_id' => $leagueId,
                'season' => $season,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get head to head statistics
     */
    public function getHeadToHead($team1Id, $team2Id)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/fixtures/headtohead', [
                'h2h' => $team1Id . '-' . $team2Id
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch head to head', [
                'team1_id' => $team1Id,
                'team2_id' => $team2Id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching head to head', [
                'team1_id' => $team1Id,
                'team2_id' => $team2Id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get league standings
     */
    public function getStandings($leagueId, $season)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/standings', [
                'league' => $leagueId,
                'season' => $season
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch standings', [
                'league_id' => $leagueId,
                'season' => $season,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching standings', [
                'league_id' => $leagueId,
                'season' => $season,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get upcoming matches
     */
    public function getUpcomingMatches($leagueId, $date)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/fixtures', [
                'league' => $leagueId,
                'date' => $date,
                'status' => 'NS' // Not Started
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('API Football: Failed to fetch upcoming matches', [
                'league_id' => $leagueId,
                'date' => $date,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('API Football: Exception while fetching upcoming matches', [
                'league_id' => $leagueId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if API is working
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
            ])->get($this->baseUrl . '/status');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('API Football: Connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
