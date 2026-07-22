<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Fixture;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function index()
    {
        $fixtures = Fixture::with('predictions')
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.index', compact('fixturesByLeague', 'fixtures'));
    }

    public function expertTips()
    {
        $predictions = Prediction::featured()
            ->with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.expert-tips', compact('predictions'));
    }

    public function category($category)
    {
        $categories = [
            '1x2' => '1X2 Predictions',
            'double_chance' => 'Double Chance',
            'over_1_5' => 'Over 1.5 Goals',
            'over_2_5' => 'Over 2.5 Goals',
            'draw' => 'Draw Predictions',
            'bts' => 'Both Teams to Score'
        ];

        $title = $categories[$category] ?? 'Predictions';

        $predictions = Prediction::byCategory($category)
            ->whereHas('fixture', function ($q) {
                $q->whereDate('match_date', today());
            })
            ->with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.category', compact('predictions', 'title', 'category'));
    }

    public function premium()
    {
        $fixtures = Fixture::with(['predictions' => function($query) {
            $query->where('is_premium', true);
        }])
        ->whereDate('match_date', today())
        ->orderBy('match_date')
        ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.premium', compact('fixturesByLeague', 'fixtures'));
    }

    public function maxodds()
    {
        $fixtures = Fixture::with(['predictions' => function($query) {
            $query->where('is_maxodds', true);
        }])
        ->maxoddsTips()
        ->whereDate('match_date', today())
        ->orderBy('match_date')
        ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.maxodds', compact('fixturesByLeague', 'fixtures'));
    }

    public function over15()
    {
        $fixtures = Fixture::with('predictions')
            ->where('over15', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.category', compact('fixturesByLeague', 'fixtures'))->with('category', 'Over 1.5');
    }

    public function over25()
    {
        $fixtures = Fixture::with('predictions')
            ->where('over25', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.category', compact('fixturesByLeague', 'fixtures'))->with('category', 'Over 2.5');
    }

    public function doubleChance()
    {
        $fixtures = Fixture::with('predictions')
            ->where('double_chance', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.category', compact('fixturesByLeague', 'fixtures'))->with('category', 'Double Chance');
    }

    public function bts()
    {
        $fixtures = Fixture::with('predictions')
            ->where('bts', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.category', compact('fixturesByLeague', 'fixtures'))->with('category', 'Both Teams to Score');
    }

    public function draw()
    {
        $fixtures = Fixture::with('predictions')
            ->where('draw', true)
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        return view('predictions.category', compact('fixturesByLeague', 'fixtures'))->with('category', 'Draw');
    }

    public function tomorrow()
    {
        $fixtures = Fixture::with('predictions')
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->get();

        $fixturesByLeague = $fixtures->groupBy('league_name');

        return view('predictions.tomorrow', compact('fixturesByLeague', 'fixtures'));
    }

    /**
     * Basketball predictions page
     */
    public function basketball()
    {
        $basketballTips = Fixture::with('predictions')
            ->where('sport_type', 'basketball')
            ->whereDate('match_date', today())
            ->orderBy('match_date')
            ->paginate(20);

        $fixturesByLeague = $basketballTips->getCollection()->groupBy('league_name');

        return view('basketball.index', compact('basketballTips', 'fixturesByLeague'));
    }
}
