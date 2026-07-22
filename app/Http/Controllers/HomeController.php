<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Fixture;
use App\Models\Result;
use App\Services\PredictionEngine;
use App\Services\ApiFootballServiceEnhanced;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected PredictionEngine $predictionEngine;
    protected ApiFootballServiceEnhanced $api;

    public function __construct(PredictionEngine $predictionEngine, ApiFootballServiceEnhanced $api)
    {
        $this->predictionEngine = $predictionEngine;
        $this->api = $api;
    }

    public function index()
    {
        $todayTips = Fixture::with('predictions')
            ->todayTips()
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->limit(5)
            ->get();

        // Fallback: show any upcoming if no today tips flagged
        if ($todayTips->isEmpty()) {
            $todayTips = Fixture::with('predictions')
                ->whereDate('match_date', today())
                ->where('status', 'NS')
                ->orderBy('match_date')
                ->limit(5)
                ->get();
        }

        // Enrich today tips with full prediction data
        foreach ($todayTips as $tip) {
            if ($tip->home_team_id && $tip->away_team_id) {
                try {
                    $tip->prediction_data = $this->predictionEngine->predictFixture($tip);
                } catch (\Exception $e) {
                    $tip->prediction_data = null;
                }
            }
        }

        $featuredPredictions = Fixture::with('predictions')
            ->featured()
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->limit(15)
            ->get();

        // Fallback: if no featured fixtures, use any upcoming fixtures
        if ($featuredPredictions->isEmpty()) {
            $featuredPredictions = Fixture::with('predictions')
                ->whereDate('match_date', today())
                ->where('status', 'NS')
                ->orderBy('match_date')
                ->limit(15)
                ->get();
        }

        $todayTipsByLeague = $todayTips->groupBy('league_name');
        $featuredByLeague = $featuredPredictions->groupBy('league_name');

        // Get VIP and VVIP results
        $vipResults = Result::where('type', 'vip')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $vvipResults = Result::where('type', 'vvip')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Get Sure Picks — fall back to best fixtures if none flagged
        $surePicksTips = Fixture::with('predictions')
            ->where('is_surepick', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->limit(4)
            ->get();

        if ($surePicksTips->isEmpty()) {
            $surePicksTips = Fixture::with('predictions')
                ->whereDate('match_date', today())
                ->where('status', 'NS')
                ->orderBy('match_date')
                ->limit(4)
                ->get();
        }

        // Enrich sure picks
        foreach ($surePicksTips as $tip) {
            if ($tip->home_team_id && $tip->away_team_id) {
                try {
                    $tip->prediction_data = $this->predictionEngine->predictFixture($tip);
                } catch (\Exception $e) {
                    $tip->prediction_data = null;
                }
            }
        }

        // Get basketball tips
        $basketballTips = Fixture::with('predictions')
            ->where('sport_type', 'basketball')
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->limit(5)
            ->get();

        // Get live scores
        try {
            $liveScores = $this->api->getLiveFixtures();
            $liveFixtures = $liveScores['response'] ?? [];
        } catch (\Exception $e) {
            $liveFixtures = [];
        }

        return view('home', compact(
            'todayTipsByLeague', 'featuredByLeague',
            'vipResults', 'vvipResults', 'surePicksTips',
            'basketballTips', 'liveFixtures'
        ));
    }

    /**
     * Get live scores JSON (for AJAX polling)
     */
    public function liveScores()
    {
        try {
            $liveScores = $this->api->getLiveFixtures();
            $liveFixtures = $liveScores['response'] ?? [];
            return response()->json(['fixtures' => $liveFixtures]);
        } catch (\Exception $e) {
            return response()->json(['fixtures' => [], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get prediction for a specific match via AJAX
     */
    public function predictMatch(Request $request)
    {
        $fixtureId = $request->input('fixture_id');
        $fixture = Fixture::where('api_fixture_id', $fixtureId)
            ->orWhere('id', $fixtureId)
            ->first();

        if (!$fixture) {
            return response()->json(['error' => 'Fixture not found'], 404);
        }

        try {
            $prediction = $this->predictionEngine->predictFixture($fixture);
            return response()->json($prediction);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dynamic XML Sitemap
     */
    public function sitemap()
    {
        $fixtures = Fixture::whereDate('match_date', today())
            ->orderBy('match_date')->limit(500)->get();

        return response()->view('sitemap', ['fixtures' => $fixtures])
            ->header('Content-Type', 'application/xml');
    }
}
