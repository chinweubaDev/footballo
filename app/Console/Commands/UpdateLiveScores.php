<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Services\ApiFootballServiceEnhanced;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateLiveScores extends Command
{
    protected $signature = 'scores:update-live 
                            {--interval=5 : Update interval in minutes}
                            {--continuous : Run continuously until stopped}
                            {--date= : Update fixtures for a specific date (YYYY-MM-DD), defaults to today}';

    protected $description = 'Update live scores for in-play and today\'s matches';

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $continuous = $this->option('continuous');
        $date = $this->option('date') ?: now()->toDateString();

        $api = app(ApiFootballServiceEnhanced::class);

        do {
            $this->info('🔄 Updating scores... ' . now()->format('H:i:s'));

            try {
                $totalUpdated = 0;
                $totalSkipped = 0;

                // ═══ PHASE 1: Live/in-play fixtures from API ═══
                $this->line('📡 Fetching live fixtures from API...');
                $liveFixtures = $api->getLiveFixtures();

                if ($liveFixtures && !empty($liveFixtures['response'])) {
                    $liveCount = count($liveFixtures['response']);
                    $this->line("   Found {$liveCount} live fixture(s)");

                    foreach ($liveFixtures['response'] as $data) {
                        $result = $this->updateFixtureScore($data);
                        if ($result === 'updated') $totalUpdated++;
                        elseif ($result === 'skipped') $totalSkipped++;
                    }
                } else {
                    $this->warn('   No live fixtures found.');
                }

                // ═══ PHASE 2: All of today's fixtures from DB (catch any missed) ═══
                $this->line("📋 Scanning today's fixtures ({$date})...");
                $todaysFixtures = Fixture::whereNotNull('api_fixture_id')
                    ->whereDate('match_date', $date)
                    ->get();

                $needsUpdate = $todaysFixtures->filter(fn($f) => $this->needsUpdate($f));
                $alreadyDone = $todaysFixtures->count() - $needsUpdate->count();

                $this->line("   Total: {$todaysFixtures->count()} | Need update: {$needsUpdate->count()} | Already current: {$alreadyDone}");

                if ($needsUpdate->isNotEmpty()) {
                    $bar = $this->output->createProgressBar($needsUpdate->count());
                    $bar->start();

                    foreach ($needsUpdate as $fixture) {
                        $fixtureData = $api->getFixturesById($fixture->api_fixture_id);
                        if ($fixtureData && !empty($fixtureData['response'])) {
                            $result = $this->updateFixtureScore($fixtureData['response'][0]);
                            if ($result === 'updated') $totalUpdated++;
                            elseif ($result === 'skipped') $totalSkipped++;
                        }
                        $bar->advance();
                    }

                    $bar->finish();
                    $this->newLine();
                }

                $this->info("✅ Done: {$totalUpdated} updated, {$totalSkipped} skipped, {$alreadyDone} already current. " . now()->format('H:i:s'));

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                Log::error('scores:update-live failed: ' . $e->getMessage());
            }

            if ($continuous) {
                $this->sleepInterval($interval);
            }
        } while ($continuous);

        return 0;
    }

    /**
     * Check if a fixture needs its score updated from the API.
     * Skip if already in a terminal state with scores populated.
     */
    protected function needsUpdate(Fixture $fixture): bool
    {
        // If already in a terminal state AND has scores, skip
        $terminalStates = ['FT', 'AET', 'PEN', 'ABD', 'WO', 'CANC'];
        
        if (in_array($fixture->status, $terminalStates) 
            && $fixture->home_goals !== null 
            && $fixture->away_goals !== null) {
            return false;
        }

        // Postponed/cancelled fixtures don't need updating
        if (in_array($fixture->status, ['PST', 'CANC', 'ABD'])) {
            return false;
        }

        // Future matches that haven't started yet — skip (not started + far in future)
        if ($fixture->status === 'NS' && $fixture->match_date->isFuture()) {
            return false;
        }

        return true;
    }

    protected function updateFixtureScore(array $data): string
    {
        $f = $data['fixture'];
        $goals = $data['goals'];
        $score = $data['score'];
        $status = $f['status']['short'];
        $elapsed = $f['status']['elapsed'] ?? null;
        $fixture = Fixture::where('api_fixture_id', $f['id'])->first();

        if (!$fixture) {
            return 'skipped'; // Fixture not in database
        }

        // Skip if nothing changed
        if ($fixture->status === $status 
            && $fixture->home_goals === $goals['home'] 
            && $fixture->away_goals === $goals['away']
            && $fixture->elapsed === $elapsed) {
            return 'skipped';
        }

        $oldStatus = $fixture->status;
        
        $fixture->update([
            'status' => $status,
            'elapsed' => $elapsed,
            'home_goals' => $goals['home'],
            'away_goals' => $goals['away'],
            'home_goals_halftime' => $score['halftime']['home'] ?? $fixture->home_goals_halftime,
            'away_goals_halftime' => $score['halftime']['away'] ?? $fixture->away_goals_halftime,
        ]);

        $this->line("   {$fixture->home_team} vs {$fixture->away_team}: {$oldStatus}→{$status} | {$goals['home']}-{$goals['away']}");

        // If match just finished, evaluate predictions
        if (in_array($status, ['FT', 'AET', 'PEN']) && !in_array($oldStatus, ['FT', 'AET', 'PEN'])) {
            $this->evaluatePredictions($fixture);
        }

        return 'updated';
    }

    protected function evaluatePredictions(Fixture $fixture): void
    {
        // Structured, multi-market resolution (Phase 1E). Replaces the legacy
        // string-matching evaluation; resolves WON/LOST/VOID for every market.
        $resolver = app(\App\Services\Prediction\Evaluation\PredictionResultService::class);
        $resolver->resolveFixturePredictions($fixture);

        // Refresh performance caches now that results changed.
        app(\App\Services\Prediction\Evaluation\PerformanceAnalyticsService::class)->flush();

        // Update the legacy Result model for VIP/VVIP tracking.
        $this->updateResultsTracking($fixture->id);
    }

    protected function updateResultsTracking(int $fixtureId): void
    {
        // This would connect to your existing Result model
        // to track VIP/VVIP performance
        $fixture = Fixture::find($fixtureId);
        
        if ($fixture->is_vip) {
            \App\Models\Result::updateOrCreate(
                ['type' => 'vip', 'date' => $fixture->match_date->toDateString()],
                ['odds' => 1.5, 'status' => $fixture->home_goals > $fixture->away_goals ? 'won' : 'lost']
            );
        }
        if ($fixture->is_vvip) {
            \App\Models\Result::updateOrCreate(
                ['type' => 'vvip', 'date' => $fixture->match_date->toDateString()],
                ['odds' => 2.0, 'status' => $fixture->home_goals > $fixture->away_goals ? 'won' : 'lost']
            );
        }
    }

    protected function sleepInterval(int $minutes): void
    {
        $this->info("⏳ Waiting {$minutes} minutes until next update...");
        sleep($minutes * 60);
    }
}
