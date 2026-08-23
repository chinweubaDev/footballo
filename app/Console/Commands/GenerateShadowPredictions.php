<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\PredictionModel;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\Prediction\PredictionEngine;
use App\Services\SystemEventService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Generates SHADOW predictions using the v1.1.0 candidate model.
 *
 * Shadow predictions are stored with model_version='v1.1.0' and
 * status='shadow', so they never appear on the public site. They exist to
 * compare v1.1.0 against live v1.0.0 before activation.
 */
class GenerateShadowPredictions extends Command
{
    protected $signature = 'predictions:shadow
                            {--date= : Date (Y-m-d, default today)}
                            {--league= : Specific league id}
                            {--days=1 : Number of days ahead (default: today only)}';

    protected $description = 'Generate shadow predictions for the v1.1.0 candidate model';

    public function handle(PredictionEngine $engine, ApiFootballServiceEnhanced $api, SystemEventService $events): int
    {
        $shadowModel = PredictionModel::where('version', 'v1.1.0')->first();

        if (! $shadowModel) {
            $this->error('v1.1.0 model not found. Run db:seed PredictionModelSeeder.');
            return 1;
        }

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $leagueId = $this->option('league') ? (int) $this->option('league') : null;
        $days = (int) $this->option('days');

        $enabledLeagueIds = League::query()
            ->where('enabled', true)
            ->where('prediction_enabled', true)
            ->pluck('api_football_league_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($enabledLeagueIds)) {
            $this->warn('No enabled prediction leagues found.');
            return 1;
        }

        $generated = 0;

        // Only fetch fixtures for enabled leagues — never the whole worldwide list.
        $targetLeagueIds = $leagueId ? [(int) $leagueId] : $enabledLeagueIds;

        for ($d = 0; $d < $days; $d++) {
            $currentDate = $date->copy()->addDays($d);

            $dayFixtures = [];

            foreach ($targetLeagueIds as $targetLeagueId) {
                // League-filtered requests require the season parameter.
                $season = $currentDate->month >= 7 ? $currentDate->year : $currentDate->year - 1;
                $data = $api->getFixturesByDate($currentDate->toDateString(), $targetLeagueId, $season);

                if (! $data || empty($data['response'])) {
                    continue;
                }

                foreach ($data['response'] as $row) {
                    $dayFixtures[] = $row;
                }
            }

            $fixtures = array_values(array_filter($dayFixtures, function ($f) use ($enabledLeagueIds) {
                $status = $f['fixture']['status']['short'] ?? 'NS';
                $league = (int) ($f['league']['id'] ?? 0);

                return in_array($status, ['NS', 'TBD', 'PST'], true) && in_array($league, $enabledLeagueIds, true);
            }));

            foreach ($fixtures as $fixtureData) {
                $apiId = $fixtureData['fixture']['id'];
                $fixture = Fixture::where('api_fixture_id', $apiId)->first();

                if (! $fixture || ! $fixture->home_team_id || ! $fixture->away_team_id) {
                    continue;
                }

                try {
                    $engine->generate($fixture, $shadowModel, 'shadow');
                    $generated++;
                } catch (\Throwable $e) {
                    $this->error("Shadow generation failed for fixture {$apiId}: ".$e->getMessage());

                    // Record the failure (e.g. "MySQL server has gone away") so
                    // it is visible in /admin/system/alerts — never hide it.
                    $events->generationFailure(
                        "Shadow generation failed for fixture {$apiId}: ".$e->getMessage(),
                        [
                            'command' => 'predictions:shadow',
                            'fixture_api_id' => $apiId,
                            'model_version' => 'v1.1.0',
                            'exception' => get_class($e),
                        ],
                    );
                }
            }
        }

        $this->info("Shadow predictions generated for {$generated} fixture(s) using v1.1.0.");

        return 0;
    }
}
