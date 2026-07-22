<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\PredictionEngine;
use Illuminate\Http\Request;

class MatchDetailController extends Controller
{
    protected ApiFootballServiceEnhanced $api;
    protected PredictionEngine $engine;

    public function __construct(ApiFootballServiceEnhanced $api, PredictionEngine $engine)
    {
        $this->api = $api;
        $this->engine = $engine;
    }

    public function show($id)
    {
        $fixture = Fixture::with('predictions')->findOrFail($id);

        // Get fresh prediction
        try {
            $prediction = $this->engine->predictFixture($fixture);
        } catch (\Exception $e) {
            $prediction = null;
        }

        // Fetch H2H
        $h2h = null;
        if ($fixture->home_team_id && $fixture->away_team_id) {
            $h2hData = $this->api->getHeadToHead($fixture->home_team_id, $fixture->away_team_id, 5);
            $h2h = $h2hData['response'] ?? [];
        }

        // Fetch standings
        $standings = null;
        if ($fixture->league_id) {
            $sData = $this->api->getStandings($fixture->league_id, $fixture->season ?? date('Y'));
            $standings = $sData['response'] ?? [];
        }

        // Fetch odds
        $odds = null;
        if ($fixture->api_fixture_id) {
            $oData = $this->api->getOdds($fixture->api_fixture_id);
            $odds = $oData['response'] ?? [];
        }

        // Fetch lineups if match has started
        $lineups = null;
        if ($fixture->api_fixture_id && $fixture->status !== 'NS') {
            $lData = $this->api->getFixtureLineups($fixture->api_fixture_id);
            $lineups = $lData['response'] ?? [];
        }

        // Fetch events if match has started
        $events = null;
        if ($fixture->api_fixture_id && $fixture->status !== 'NS') {
            $eData = $this->api->getFixtureEvents($fixture->api_fixture_id);
            $events = $eData['response'] ?? [];
        }

        return view('match.detail', compact(
            'fixture', 'prediction', 'h2h', 'standings', 'odds', 'lineups', 'events'
        ));
    }
}
