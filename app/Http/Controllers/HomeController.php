<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Fixture;
use App\Models\Result;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $todayTips = Fixture::with('predictions')
            ->todayTips()
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->limit(5)
            ->get();
    
        $featuredPredictions = Fixture::with('predictions')
            ->featured()
            ->whereDate('match_date', '>=', today())
            ->orderBy('match_date')
            ->limit(20)
            ->get();
    
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
        
        // Get Sure Picks tips with fixture data for logos
        $surePicksTips = \App\Models\Tip::leftJoin('fixtures', 'tips.fixture_id', '=', 'fixtures.api_fixture_id')
            ->where('tips.category', 'surepick')
            ->where('tips.is_active', true)
            ->whereDate('tips.match_date', '>=', today())
            ->orderBy('tips.match_date')
            ->limit(5)
            ->select('tips.*', 'fixtures.home_team_logo', 'fixtures.away_team_logo', 'fixtures.league_logo')
            ->get();
    
        return view('home', compact('todayTipsByLeague', 'featuredByLeague', 'vipResults', 'vvipResults', 'surePicksTips'));
    }
    
}
