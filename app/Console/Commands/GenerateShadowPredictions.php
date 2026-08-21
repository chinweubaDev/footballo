<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\PredictionModel;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\Prediction\PredictionEngine;
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
                            {--days=3 : Number of days ahead}';

    protected $description = 'Generate shadow predictions for the v1.1.0 candidate model';

    public function handle(PredictionEngine $engine, ApiFootballServiceEnhanced $api): int
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
            ->all();

        if (empty($enabledLeagueIds)) {
            $this->warn('No enabled prediction leagues found.');
            return 1;
        }

        $generated = 0;

        for ($d = 0; $d < $days; $d++) {
            $currentDate = $date->copy()->addDays($d);
            $season = $leagueId ? ($currentDate->month >= 7 ? $currentDate->year : $currentDate->year - 1) : null;

            $data = $api->getFixturesByDate($currentDate->toDateString(), $leagueId, $season);

            if (! $data || empty($data['response'])) {
                continue;
            }

            $fixtures = array_values(array_filter($data['response'], function ($f) use ($enabledLeagueIds) {
                $status = $f['fixture']['status']['short'] ?? 'NS';
                $league = $f['league']['id'] ?? 0;

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
                }
            }
        }

        $this->info("Shadow predictions generated for {$generated} fixture(s) using v1.1.0.");

        return 0;
    }
}
