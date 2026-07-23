<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fixture;
use Illuminate\Support\Collection;

class TipsController extends Controller
{
    /**
     * Build accumulator tickets from the highest confidence predictions.
     *
     * @param string $type 'vip' or 'vvip'
     * @param int    $ticketCount  number of accumulator tickets (3 for VIP, 5 for VVIP)
     * @param int    $legsPerTicket  number of matches per accumulator (3)
     * @return array  ['tickets' => [...], 'totalPicks' => int]
     */
    private function buildAccumulators(string $type, int $ticketCount, int $legsPerTicket = 3): array
    {
        $totalNeeded = $ticketCount * $legsPerTicket;

        // Fetch top confidence predictions for today
        $fixtures = Fixture::with('predictions')
            ->where('is_vip', true)
            ->whereDate('match_date', today())
            ->get();

        if ($type === 'vvip') {
            $fixtures = Fixture::with('predictions')
                ->where('is_vvip', true)
                ->whereDate('match_date', today())
                ->get();
        }

        // Build a scored list: each entry = match + confidence + odds
        $scored = [];
        foreach ($fixtures as $fixture) {
            $prediction = $fixture->predictions->first();
            if (!$prediction || !$prediction->confidence || !$prediction->odds) {
                continue;
            }
            $scored[] = [
                'fixture' => $fixture,
                'prediction' => $prediction,
                'confidence' => (int) $prediction->confidence,
                'odds' => (float) $prediction->odds,
            ];
        }

        // Sort by confidence descending
        usort($scored, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        // Take only the top needed picks
        $topPicks = array_slice($scored, 0, $totalNeeded);

        // Group into accumulator tickets
        $tickets = [];
        for ($i = 0; $i < $ticketCount; $i++) {
            $legs = array_slice($topPicks, $i * $legsPerTicket, $legsPerTicket);
            if (count($legs) === 0) {
                continue;
            }
            // Calculate combined odds (multiply all leg odds)
            $totalOdds = 1;
            foreach ($legs as $leg) {
                $totalOdds *= $leg['odds'];
            }
            $totalOdds = round($totalOdds, 2);

            // Average confidence across legs
            $avgConfidence = round(array_sum(array_column($legs, 'confidence')) / count($legs), 1);

            $tickets[] = [
                'legs' => $legs,
                'total_odds' => $totalOdds,
                'avg_confidence' => $avgConfidence,
                'ticket_number' => $i + 1,
            ];
        }

        return [
            'tickets' => $tickets,
            'totalPicks' => count($topPicks),
        ];
    }

    public function vip()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access VIP tips.');
        }

        // Check if user has active VIP subscription
        if (!Auth::user()->hasActiveVIP()) {
            return redirect()->route('pricing')->with('error', 'You need an active VIP subscription to access VIP tips.');
        }

        // Build 3 VIP accumulator tickets, 3 legs each = top 9 picks
        $accumulator = $this->buildAccumulators('vip', 3, 3);

        return view('tips.vip', [
            'tickets' => $accumulator['tickets'],
            'totalPicks' => $accumulator['totalPicks'],
        ]);
    }

    public function vvip()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access VVIP tips.');
        }

        // Check if user has active VVIP subscription
        if (!Auth::user()->hasActiveVVIP()) {
            return redirect()->route('pricing')->with('error', 'You need an active VVIP subscription to access VVIP tips.');
        }

        // Build 5 VVIP accumulator tickets, 3 legs each = top 15 picks
        $accumulator = $this->buildAccumulators('vvip', 5, 3);

        return view('tips.vvip', [
            'tickets' => $accumulator['tickets'],
            'totalPicks' => $accumulator['totalPicks'],
        ]);
    }
}
