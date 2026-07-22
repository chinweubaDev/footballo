<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Prediction;
use Illuminate\Support\Collection;

/**
 * Categorizes predictions into different tip types
 * - Today's Tips (5 top trending matches)
 * - Sure Picks (4 surest predictions)
 * - Featured Tips (15 standout matches)
 * - VIP Tips (5 best odds daily)
 * - VVIP Tips (5 best odds daily, premium)
 * 
 * Accepts pre-computed predictions to avoid redundant API calls
 */
class TipCategorizer
{
    /**
     * Categorize fixtures using pre-computed predictions
     * Each fixture should already have ->prediction_data set by the engine
     */
    public function categorizeFixtures(Collection $fixtures, array $precomputedPredictions = []): array
    {
        $predictions = [];

        foreach ($fixtures as $fixture) {
            // Use precomputed predictions if available, else use fixture's prediction_data
            $pred = $precomputedPredictions[$fixture->id] 
                ?? $fixture->prediction_data 
                ?? null;
            
            if (!$pred || !isset($pred['1x2'])) {
                continue; // Skip fixtures without predictions
            }
            
            $predictions[] = [
                'fixture' => $fixture,
                'prediction' => $pred,
            ];
        }

        if (empty($predictions)) {
            return [
                'today_tips' => [],
                'sure_picks' => [],
                'featured_tips' => [],
                'vip_tips' => [],
                'vvip_tips' => [],
            ];
        }

        // Sort by confidence (1X2)
        usort($predictions, function ($a, $b) {
            $confA = max($a['prediction']['1x2']['probabilities']);
            $confB = max($b['prediction']['1x2']['probabilities']);
            return $confB <=> $confA;
        });

        // Filter out low-confidence predictions
        $qualityPredictions = array_filter($predictions, function ($p) {
            $maxConf = max($p['prediction']['1x2']['probabilities']);
            return $maxConf >= 40;
        });
        $qualityPredictions = array_values($qualityPredictions);

        // ─── Today's Tips: Top 5 highest confidence ───
        $todayTips = array_slice($qualityPredictions, 0, min(5, count($qualityPredictions)));

        // ─── Sure Picks: next 4 after today tips ───
        $excludeIds = [];
        foreach ($todayTips as $e) { $excludeIds[$e['fixture']->id] = true; }
        $remaining = array_filter($qualityPredictions, fn($p) => !isset($excludeIds[$p['fixture']->id]));
        $surePicks = array_slice(array_values($remaining), 0, 4);

        // ─── Featured: all remaining ───
        foreach ($surePicks as $e) { $excludeIds[$e['fixture']->id] = true; }
        $featuredCandidates = array_filter($qualityPredictions, fn($p) => !isset($excludeIds[$p['fixture']->id]));
        $featuredTips = $this->selectDiverseLeagues(array_values($featuredCandidates), 15);

        // ─── VIP: best of remaining ───
        foreach ($featuredTips as $e) { $excludeIds[$e['fixture']->id] = true; }
        $vipCandidates = array_filter($qualityPredictions, fn($p) => !isset($excludeIds[$p['fixture']->id]));
        $vipTips = $this->selectByCompositeScore(array_values($vipCandidates), 5, 0.5, 0.3, 0.2);

        // ─── VVIP: best of remaining ───
        $vvipIds = [];
        foreach ($vipTips as $v) { $vvipIds[$v['fixture']->id] = true; }
        $vvipCandidates = array_filter($qualityPredictions, function ($p) use ($excludeIds, $vvipIds) {
            return !isset($excludeIds[$p['fixture']->id]) && !isset($vvipIds[$p['fixture']->id]);
        });
        $vvipTips = $this->selectByCompositeScore(array_values($vvipCandidates), 5, 0.6, 0.4, 0);

        return [
            'today_tips' => $todayTips,
            'sure_picks' => $surePicks,
            'featured_tips' => $featuredTips,
            'vip_tips' => $vipTips,
            'vvip_tips' => $vvipTips,
        ];
    }

    /**
     * Select tips by composite score (1X2 confidence × w1 + over25 × w2 + bts × w3)
     */
    protected function selectByCompositeScore(array $candidates, int $count, float $w1, float $w2, float $w3): array
    {
        if (empty($candidates)) return [];
        
        $scored = [];
        foreach ($candidates as $p) {
            $score = max($p['prediction']['1x2']['probabilities']) * $w1
                + ($p['prediction']['over25']['probability'] ?? 50) * $w2
                + ($p['prediction']['bts']['probability'] ?? 50) * $w3;
            $scored[] = ['data' => $p, 'score' => $score];
        }
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_map(fn($x) => $x['data'], array_slice($scored, 0, $count));
    }

    /**
     * Select diverse league matches
     */
    protected function selectDiverseLeagues(array $predictions, int $count): array
    {
        $selected = [];
        $usedLeagues = [];

        foreach ($predictions as $p) {
            if (count($selected) >= $count) break;
            $leagueId = $p['fixture']->league_id;

            $leagueCount = array_count_values($usedLeagues)[$leagueId] ?? 0;
            if ($leagueCount >= 3) continue; // Max 3 per league

            $selected[] = $p;
            $usedLeagues[] = $leagueId;
        }

        return $selected;
    }
}
