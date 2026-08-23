<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Services\ApiFootballServiceEnhanced;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Phase 1K — fixture synchronization as its own pipeline stage.
 *
 *   php artisan predictions:sync-fixtures --days=1
 */
class SyncFixtures extends Command
{
    protected $signature = 'predictions:sync-fixtures
                            {--date= : Date (Y-m-d, default today)}
                            {--days=1 : Number of days ahead (default: today only)}';

    protected $description = 'Synchronize upcoming fixtures from API-Football (idempotent)';

    public function handle(ApiFootballServiceEnhanced $api): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $days = (int) $this->option('days');

        $leagueIds = League::query()
            ->where('enabled', true)
            ->where('prediction_enabled', true)
            ->pluck('api_football_league_id')
            ->all();

        if (empty($leagueIds)) {
            $this->warn('No enabled prediction leagues.');
            return 1;
        }

        $synced = 0;

        for ($d = 0; $d < $days; $d++) {
            $current = $date->copy()->addDays($d);

            foreach ($leagueIds as $leagueId) {
                $season = $current->month >= 7 ? $current->year : $current->year - 1;
                $data = $api->getFixturesByDate($current->toDateString(), $leagueId, $season);

                if (! $data || empty($data['response'])) {
                    continue;
                }

                foreach ($data['response'] as $row) {
                    $f = $row['fixture'];
                    $teams = $row['teams'];
                    $lg = $row['league'];
                    $goals = $row['goals'];

                    if (! in_array($f['status']['short'] ?? 'NS', ['NS', 'TBD', 'PST'], true)) {
                        continue;
                    }

                    Fixture::updateOrCreate(
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
                            'venue_name' => $f['venue']['name'] ?? null,
                            'venue_city' => $f['venue']['city'] ?? null,
                            'status' => $f['status']['short'] ?? 'NS',
                            'home_goals' => $goals['home'] ?? null,
                            'away_goals' => $goals['away'] ?? null,
                        ]
                    );

                    $synced++;
                }
            }
        }

        $this->info("Synced {$synced} fixture(s).");

        return 0;
    }
}
