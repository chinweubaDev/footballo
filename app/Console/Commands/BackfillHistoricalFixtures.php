<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Services\ApiFootballServiceEnhanced;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Backfills historical fixtures (with final scores) from API-Football for a
 * league+season. Used to build multi-league / multi-season validation data.
 * Never fabricates data — if the API has no fixtures, nothing is inserted.
 */
class BackfillHistoricalFixtures extends Command
{
    protected $signature = 'predictions:backfill
                            {--league= : API-Football league id (omit for all enabled leagues)}
                            {--season=2025 : Season year (e.g. 2025 for 2025/26)}';

    protected $description = 'Backfill historical fixtures (with scores) from API-Football';

    public function handle(ApiFootballServiceEnhanced $api): int
    {
        $season = (int) $this->option('season');
        $leagueOption = $this->option('league');

        $leagueIds = $leagueOption
            ? [(int) $leagueOption]
            : League::query()->where('enabled', true)->where('prediction_enabled', true)->pluck('api_football_league_id')->all();

        if (empty($leagueIds)) {
            $this->warn('No leagues to backfill.');
            return 1;
        }

        $totalInserted = 0;

        foreach ($leagueIds as $leagueId) {
            $data = $api->getFixturesByLeagueSeason($leagueId, $season);

            if (! $data || empty($data['response'])) {
                $this->warn("No fixtures for league={$leagueId} season={$season}. errors=".json_encode($data['errors'] ?? null));
                continue;
            }

            $inserted = 0;

            foreach ($data['response'] as $r) {
                $this->saveFixture($r, $season);
                $inserted++;
            }

            $this->info("League {$leagueId} season {$season}: {$inserted} fixtures stored.");
            $totalInserted += $inserted;
        }

        $this->info("Done. Total fixtures stored: {$totalInserted}");

        return 0;
    }

    protected function saveFixture(array $r, int $season): Fixture
    {
        $f = $r['fixture'];
        $teams = $r['teams'];
        $lg = $r['league'];
        $goals = $r['goals'];
        $score = $r['score'];

        $status = $f['status']['short'] ?? 'NS';
        $homeGoals = $goals['home'] ?? null;
        $awayGoals = $goals['away'] ?? null;

        return Fixture::updateOrCreate(
            ['api_fixture_id' => $f['id']],
            [
                'league_id' => $lg['id'] ?? null,
                'league_name' => $lg['name'] ?? '',
                'league_country' => $lg['country'] ?? '',
                'league_logo' => $lg['logo'] ?? null,
                'league_flag' => $lg['flag'] ?? null,
                'season' => $lg['season'] ?? $season,
                'round' => $lg['round'] ?? null,
                'home_team' => $teams['home']['name'] ?? '',
                'away_team' => $teams['away']['name'] ?? '',
                'home_team_logo' => $teams['home']['logo'] ?? null,
                'away_team_logo' => $teams['away']['logo'] ?? null,
                'home_team_id' => $teams['home']['id'] ?? null,
                'away_team_id' => $teams['away']['id'] ?? null,
                'match_date' => Carbon::parse($f['date']),
                'status' => $status,
                'home_goals' => $homeGoals,
                'away_goals' => $awayGoals,
                'home_goals_halftime' => $score['halftime']['home'] ?? null,
                'away_goals_halftime' => $score['halftime']['away'] ?? null,
            ]
        );
    }
}
