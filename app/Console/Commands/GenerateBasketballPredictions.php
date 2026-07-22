<?php

namespace App\Console\Commands;

use App\Services\BasketballApiService;
use App\Services\BasketballPredictionEngine;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateBasketballPredictions extends Command
{
    protected $signature = 'predictions:basketball 
                            {--date= : Date (Y-m-d, default: today)}
                            {--league= : League ID (default: 12 = NBA)}';

    protected $description = 'Fetch and predict basketball games';

    public function handle(): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $leagueId = (int) ($this->option('league') ?: 12); // NBA default

        $this->info("🏀 Generating Basketball Predictions for {$date->toDateString()}");

        $api = app(BasketballApiService::class);
        $engine = app(BasketballPredictionEngine::class);

        $games = $api->getGamesByDate($date->toDateString(), $leagueId);

        if (!$games || empty($games['response'])) {
            $this->warn('No basketball games found for this date.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($games['response']));
        $bar->start();

        foreach ($games['response'] as $gameData) {
            try {
                $homeId = $gameData['teams']['home']['id'];
                $awayId = $gameData['teams']['away']['id'];
                $leagueId = $gameData['league']['id'];
                $season = $gameData['league']['season'] ?? date('Y');

                $prediction = $engine->predictGame($gameData, $homeId, $awayId, $leagueId, $season);

                $this->saveBasketballPrediction($gameData, $prediction);

                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nError: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Basketball predictions generated!');

        return 0;
    }

    protected function saveBasketballPrediction(array $gameData, array $prediction): void
    {
        $game = $gameData['game'] ?? $gameData;

        // Save or update fixture
        $fixture = \App\Models\Fixture::firstOrCreate(
            ['api_fixture_id' => $game['id'] ?? 0],
            [
                'league_name' => $gameData['league']['name'] ?? 'NBA',
                'league_country' => $gameData['country']['name'] ?? 'USA',
                'league_logo' => $gameData['league']['logo'] ?? '',
                'league_id' => $gameData['league']['id'] ?? 12,
                'season' => $gameData['league']['season'] ?? date('Y'),
                'home_team' => $gameData['teams']['home']['name'] ?? '',
                'away_team' => $gameData['teams']['away']['name'] ?? '',
                'home_team_logo' => $gameData['teams']['home']['logo'] ?? '',
                'away_team_logo' => $gameData['teams']['away']['logo'] ?? '',
                'home_team_id' => $gameData['teams']['home']['id'] ?? null,
                'away_team_id' => $gameData['teams']['away']['id'] ?? null,
                'match_date' => Carbon::parse($game['date'] ?? now()),
                'status' => $game['status']['short'] ?? 'NS',
                'home_goals' => $gameData['scores']['home']['total'] ?? null,
                'away_goals' => $gameData['scores']['away']['total'] ?? null,
            ]
        );

        // Save prediction
        \App\Models\Prediction::updateOrCreate(
            ['fixture_id' => $fixture->id, 'category' => 'basketball'],
            [
                'tip' => 'Money Line: ' . $prediction['money_line']['pick'],
                'confidence' => $prediction['confidence_pct'],
                'odds' => 1.9,
                'analysis' => $prediction['analysis'],
                'is_premium' => false,
                'status' => 'pending',
                'today_tip_content' => "🏀 " . $prediction['money_line']['pick'] . " to win ({$prediction['confidence_pct']}%)",
                'featured_tip_content' => $prediction['analysis'],
            ]
        );
    }
}
