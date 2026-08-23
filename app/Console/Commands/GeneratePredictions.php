<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\Prediction\PredictionEngine;
use App\Services\SystemEventService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GeneratePredictions extends Command
{
    protected $signature = 'predictions:generate 
                            {--date= : Date to generate predictions for (Y-m-d, default: today)}
                            {--league= : Specific league ID}
                            {--days=1 : Number of days ahead to generate (default: today only)}
                            {--force : Force regenerate existing predictions}
                            {--all : Include ALL leagues (default: top leagues only)}';

    protected $description = 'Fetch fixtures and generate statistical predictions for all enabled markets';

    // @deprecated Legacy league id list. The engine now reads enabled leagues from the database.
    protected array $topLeagueIds = [
        // UEFA Competitions
        2, 3, 848,     
        // England
        39, 40,        
        // Spain
        140, 141,      
        // Germany
        78, 79,        
        // Italy
        135, 136,      
        // France
        61, 62,        
        // Netherlands
        88,            
        // Portugal
        94,            
        // Turkey
        203,           
        // USA/Canada — MLS (summer league!)
        253,           
        // Mexico — Liga MX (summer league!)
        262,           
        // Brazil — Serie A & B (summer leagues!)
        71, 72,        
        // Argentina
        128,           
        // Japan — J1 League (summer league!)
        98,            
        // South Korea — K League (summer league!)
        292,           
        // Saudi Pro League
        307,           
        // Sweden — Allsvenskan (summer league!)
        113,           
        // Norway — Eliteserien (summer league!)
        103,           
        // China — Super League (summer league!)
        169,           
        // Australia — A-League
        188,           
        // Chile
        265,           
        // Colombia
        239,           
        // Uruguay
        268,           
        // Egypt
        333,           
        // South Africa
        350,           
        // Nigeria
        357,           
        // International / Friendlies
        10,            
    ];

    public function handle(): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $leagueId = $this->option('league');
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info('🔮 Starting prediction generation...');
        $this->info("Date: {$date->toDateString()}, Days ahead: {$days}" . ($leagueId ? ", League: {$leagueId}" : ''));

        $api = app(ApiFootballServiceEnhanced::class);
        $engine = app(PredictionEngine::class);
        $events = app(SystemEventService::class);

        $enabledLeagueIds = League::query()
            ->where('enabled', true)
            ->where('prediction_enabled', true)
            ->pluck('api_football_league_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($enabledLeagueIds)) {
            $this->warn('No enabled prediction leagues found. Enable a league first.');
            return 1;
        }

        // Check remaining API requests (skip warning if --no-interaction)
        $remaining = $api->getRemainingRequests();
        if ($remaining <= 10 && $remaining > 0) {
            $this->warn("⚠️ Only {$remaining} API requests remaining.");
        }
        // Don't block — the API status call may return stale cached 0

        $totalGenerated = 0;
        $totalFailed = 0;

        // Only fetch fixtures for the leagues we actually use — never the whole
        // worldwide fixture list for a single date.
        $targetLeagueIds = $leagueId ? [(int) $leagueId] : $enabledLeagueIds;

        for ($d = 0; $d < $days; $d++) {
            $currentDate = $date->copy()->addDays($d);
            $this->info("\n📅 Processing: {$currentDate->toDateString()}");

            $dayFixtures = [];

            foreach ($targetLeagueIds as $targetLeagueId) {
                // League-filtered requests require the season parameter.
                $season = $this->seasonForDate($currentDate);
                $fixturesData = $api->getFixturesByDate($currentDate->toDateString(), $targetLeagueId, $season);

                if (!$fixturesData || empty($fixturesData['response'])) {
                    continue;
                }

                foreach ($fixturesData['response'] as $row) {
                    $dayFixtures[] = $row;
                }
            }

            // Safety net: keep only not-started fixtures from enabled leagues.
            $fixtures = array_values(array_filter($dayFixtures, function ($f) use ($enabledLeagueIds) {
                $status = $f['fixture']['status']['short'] ?? 'NS';
                $league = (int) ($f['league']['id'] ?? 0);

                return in_array($status, ['NS', 'TBD', 'PST'], true) && in_array($league, $enabledLeagueIds, true);
            }));

            $count = count($fixtures);
            $this->info("Found {$count} fixture(s) from ".count($targetLeagueIds).' enabled league(s)');

            if ($count === 0) {
                continue;
            }

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($fixtures as $fixtureData) {
                try {
                    // Save/update fixture in database
                    $fixture = $this->saveFixture($fixtureData, $force);

                    // Generate prediction for this fixture
                    if ($fixture && $fixture->home_team_id && $fixture->away_team_id) {
                        $engine->generate($fixture);
                        $totalGenerated++;
                    }

                    $bar->advance();
                } catch (\Exception $e) {
                    $this->error("\nError with fixture {$fixtureData['fixture']['id']}: " . $e->getMessage());
                    $totalFailed++;

                    $events->generationFailure(
                        "Prediction generation failed for fixture {$fixtureData['fixture']['id']}: ".$e->getMessage(),
                        [
                            'command' => 'predictions:generate',
                            'fixture_api_id' => $fixtureData['fixture']['id'] ?? null,
                            'model_version' => 'v1.0.0',
                            'exception' => get_class($e),
                        ],
                    );
                }
            }

            $bar->finish();
            $this->newLine();

        }

        $this->flagFixtures();

        $this->newLine();
        $this->info("✅ Prediction generation complete!");
        $this->info("Generated: {$totalGenerated} | Failed: {$totalFailed}");
        $this->info("API requests remaining: {$api->getRemainingRequests()}");

        return 0;
    }

    /**
     * API-Football season for a given date (European Aug–May convention):
     * July onwards is the new season (e.g. 2026-08 -> 2026),
     * January–June belongs to the previous season (e.g. 2026-03 -> 2025).
     */
    protected function seasonForDate(Carbon $date): int
    {
        return $date->month >= 7 ? $date->year : $date->year - 1;
    }

    protected function saveFixture(array $data, bool $force): ?Fixture
    {
        $f = $data['fixture'];
        $teams = $data['teams'];
        $league = $data['league'];
        $goals = $data['goals'];

        $fixture = Fixture::where('api_fixture_id', $f['id'])->first();

        if ($fixture && !$force) {
            return $fixture;
        }

        $attributes = [
            'api_fixture_id' => $f['id'],
            'league_name' => $league['name'] ?? '',
            'league_country' => $league['country'] ?? '',
            'league_logo' => $league['logo'] ?? '',
            'league_flag' => $league['flag'] ?? null,
            'league_id' => $league['id'] ?? null,
            'season' => $league['season'] ?? date('Y'),
            'round' => $league['round'] ?? '',
            'home_team' => $teams['home']['name'] ?? '',
            'away_team' => $teams['away']['name'] ?? '',
            'home_team_logo' => $teams['home']['logo'] ?? '',
            'away_team_logo' => $teams['away']['logo'] ?? '',
            'home_team_id' => $teams['home']['id'] ?? null,
            'away_team_id' => $teams['away']['id'] ?? null,
            'match_date' => Carbon::parse($f['date']),
            'venue_name' => $f['venue']['name'] ?? null,
            'venue_city' => $f['venue']['city'] ?? null,
            'status' => $f['status']['short'] ?? 'NS',
            'home_goals' => $goals['home'] ?? null,
            'away_goals' => $goals['away'] ?? null,
            'home_goals_halftime' => $data['score']['halftime']['home'] ?? null,
            'away_goals_halftime' => $data['score']['halftime']['away'] ?? null,
        ];

        if ($fixture) {
            $fixture->update($attributes);
        } else {
            $fixture = Fixture::create($attributes);
        }

        return $fixture;
    }

    protected function savePredictions(Fixture $fixture, array $predictionData, bool $force): void
    {
        if ($force) {
            Prediction::where('fixture_id', $fixture->id)->delete();
        }

        $existing = Prediction::where('fixture_id', $fixture->id)->first();
        if ($existing && !$force) {
            return;
        }

        $x12 = $predictionData['1x2'];
        $dc = $predictionData['double_chance'];
        $bts = $predictionData['bts'];
        $over15 = $predictionData['over15'];
        $over25 = $predictionData['over25'];

        Prediction::create([
            'fixture_id' => $fixture->id,
            'category' => '1x2',
            'tip' => $x12['label'],
            'confidence' => $x12['confidence'],
            'odds' => $this->estimateOdds($predictionData),
            'analysis' => $predictionData['analysis'],
            'is_premium' => false,
            'is_maxodds' => false,
            'status' => 'pending',

            'today_tip_content' => "**{$x12['pick']}** — {$fixture->home_team} vs {$fixture->away_team} ({$x12['confidence']}%)",
            'featured_tip_content' => $predictionData['analysis'],
            'vip_tip_content' => "🏆 **{$x12['label']}** | O2.5: {$over25['pick']} | BTS: {$bts['pick']}",
            'vvip_tip_content' => "💎 **{$x12['label']}** | DC: {$dc['pick']} | O2.5: {$over25['pick']} | CS: {$predictionData['correct_score']['most_likely']}",
            'surepick_tip_content' => "✅ {$x12['label']} ({$x12['confidence']}%) — {$fixture->home_team} vs {$fixture->away_team}",
            'maxodds_tip_content' => "📈 {$x12['label']} + Over 2.5 Goals",

            'over15_tip_content' => $over15['pick'],           // "Over" or "Under"
            'over25_tip_content' => $over25['pick'],           // "Over" or "Under"
            'double_chance_tip_content' => $dc['pick'],        // "1X" or "12" or "X2"
            'bts_tip_content' => $bts['pick'] === 'Yes' ? 'GG' : 'NG',  // "GG" or "NG"
            'draw_tip_content' => 'X',                         // "X"
        ]);
    }

    protected function applyCategories(array $categories): void
    {
        $todayDate = now()->toDateString();
        
        // Step 1: Reset ALL category flags for today's fixtures
        Fixture::whereDate('match_date', $todayDate)->update([
            'today_tip' => false,
            'featured' => false,
            'is_surepick' => false,
            'is_vip' => false,
            'is_vvip' => false,
            'maxodds_tip' => false,
            'over15' => false,
            'over25' => false,
            'bts' => false,
            'draw' => false,
            'double_chance' => false,
        ]);
        
        $todayFixtureIds = Fixture::whereDate('match_date', $todayDate)->pluck('id');
        Prediction::whereIn('fixture_id', $todayFixtureIds)->update([
            'is_premium' => false,
            'is_maxodds' => false,
        ]);

        // Track used fixture IDs so categories don't repeat
        $usedIds = [];

        // Step 2: Today's Tips (5) — highest confidence
        foreach ($categories['today_tips'] as $tip) {
            $id = $tip['fixture']->id;
            Fixture::where('id', $id)->update(['today_tip' => true]);
            $usedIds[$id] = true;
        }

        // Step 3: Sure Picks (4) — different from today
        foreach ($categories['sure_picks'] as $tip) {
            $id = $tip['fixture']->id;
            if (!isset($usedIds[$id])) {
                Fixture::where('id', $id)->update(['is_surepick' => true]);
                $usedIds[$id] = true;
            }
        }

        // Step 4: Featured (15) — different from above
        foreach ($categories['featured_tips'] as $tip) {
            $id = $tip['fixture']->id;
            if (!isset($usedIds[$id])) {
                Fixture::where('id', $id)->update(['featured' => true]);
                $usedIds[$id] = true;
            }
        }

        // Step 5: VIP (5) + premium
        foreach ($categories['vip_tips'] as $tip) {
            $id = $tip['fixture']->id;
            Fixture::where('id', $id)->update(['is_vip' => true]);
            Prediction::where('fixture_id', $id)->update(['is_premium' => true]);
            $usedIds[$id] = true;
        }

        // Step 6: VVIP (5) + premium + maxodds
        foreach ($categories['vvip_tips'] as $tip) {
            $id = $tip['fixture']->id;
            Fixture::where('id', $id)->update(['is_vvip' => true, 'maxodds_tip' => true]);
            Prediction::where('fixture_id', $id)->update(['is_premium' => true, 'is_maxodds' => true]);
            $usedIds[$id] = true;
        }

        // Step 7: Assign unique fixtures evenly across Over 1.5, Over 2.5, BTTS, Draw, Double Chance
        $allFixtures = Fixture::whereDate('match_date', $todayDate)
            ->whereNotIn('id', array_keys($usedIds))
            ->where('status', 'NS')
            ->get();

        $subcategories = ['over15', 'over25', 'bts', 'draw', 'double_chance'];
        $maxPerSub = [8, 8, 6, 5, 6];
        $counts = array_fill(0, 5, 0);

        foreach ($allFixtures as $fixture) {
            // Find the category with the fewest assignments (round-robin)
            $minIdx = 0;
            for ($i = 1; $i < 5; $i++) {
                if ($counts[$i] < $counts[$minIdx] && $counts[$i] < $maxPerSub[$i]) {
                    $minIdx = $i;
                }
            }
            
            if ($counts[$minIdx] < $maxPerSub[$minIdx]) {
                $flag = $subcategories[$minIdx];
                Fixture::where('id', $fixture->id)->update([$flag => true]);
                $usedIds[$fixture->id] = true;
                $counts[$minIdx]++;
            }
        }

        $this->info('✅ Categories: ' . 
            count($categories['today_tips']) . ' today | ' .
            count($categories['sure_picks']) . ' sure | ' .
            count($categories['featured_tips']) . ' featured | ' .
            count($categories['vip_tips']) . ' VIP | ' .
            count($categories['vvip_tips']) . ' VVIP | ' .
            Fixture::whereDate('match_date', $todayDate)->where('over15', true)->count() . ' O1.5 | ' .
            Fixture::whereDate('match_date', $todayDate)->where('over25', true)->count() . ' O2.5 | ' .
            Fixture::whereDate('match_date', $todayDate)->where('bts', true)->count() . ' BTTS | ' .
            Fixture::whereDate('match_date', $todayDate)->where('draw', true)->count() . ' Draw | ' .
            Fixture::whereDate('match_date', $todayDate)->where('double_chance', true)->count() . ' DC');
    }

    protected function estimateOdds(array $predictionData): float
    {
        // Use real bookmaker odds if available, else estimate
        $x12 = $predictionData['1x2'];
        $odds = $x12['best_odds'] ?? 0;
        if ($odds > 0) return round($odds, 2);

        // Fallback estimate
        $pick = $x12['pick'] ?? '1';
        return match ($pick) {
            '1' => round(1.5 + mt_rand(0, 50) / 100, 2),
            'X' => round(2.5 + mt_rand(0, 150) / 100, 2),
            '2' => round(2.0 + mt_rand(0, 200) / 100, 2),
            default => round(1.8 + mt_rand(0, 100) / 100, 2),
        };
    }

    /**
     * Update fixture homepage/category flags from published predictions so the
     * existing homepage and market pages keep working (Phase 1B compat).
     */
    protected function flagFixtures(): void
    {
        $todayDate = now()->toDateString();

        Fixture::whereDate('match_date', $todayDate)->update([
            'today_tip' => false,
            'featured' => false,
            'is_surepick' => false,
            'is_vip' => false,
            'is_vvip' => false,
            'maxodds_tip' => false,
            'over15' => false,
            'over25' => false,
            'bts' => false,
            'draw' => false,
            'double_chance' => false,
        ]);

        // Homepage sections: rank published 1X2 selections by confidence.
        $published = Prediction::query()
            ->where('market_code', '1x2')
            ->where('status', 'published')
            ->whereHas('fixture', fn ($q) => $q->whereDate('match_date', $todayDate))
            ->orderByDesc('confidence')
            ->pluck('fixture_id');

        foreach ($published as $index => $fixtureId) {
            if ($index < 5) {
                Fixture::where('id', $fixtureId)->update(['today_tip' => true]);
            } elseif ($index < 9) {
                Fixture::where('id', $fixtureId)->update(['is_surepick' => true]);
            } elseif ($index < 24) {
                Fixture::where('id', $fixtureId)->update(['featured' => true]);
            }
        }

        // Market pages: flag fixtures whose market is published.
        $categoryFlags = [
            'over_1_5' => ['over15', 8],
            'over_2_5' => ['over25', 8],
            'btts' => ['bts', 6],
            'draw' => ['draw', 5],
            'double_chance' => ['double_chance', 6],
        ];

        foreach ($categoryFlags as $marketCode => [$flag, $limit]) {
            $ids = Prediction::query()
                ->where('market_code', $marketCode)
                ->where('status', 'published')
                ->whereHas('fixture', fn ($q) => $q->whereDate('match_date', $todayDate))
                ->orderByDesc('confidence')
                ->limit($limit)
                ->pluck('fixture_id');

            foreach ($ids as $fixtureId) {
                Fixture::where('id', $fixtureId)->update([$flag => true]);
            }
        }

        $this->info('✅ Homepage & category flags updated.');
    }
}
