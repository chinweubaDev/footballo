<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Services\Prediction\PublicPredictionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PredictionController extends Controller
{
    public function __construct(protected PublicPredictionService $public)
    {
    }

    public function index(Request $request)
    {
        $query = Fixture::with('predictions')
            ->whereDate('match_date', today());

        // Filter by league
        if ($request->league) {
            $query->where('league_name', $request->league);
        }

        // Filter by prediction category
        if ($request->category) {
            $catMap = [
                '1X2' => '1X2',
                'Over/Under' => 'Over/Under',
                'Both Teams to Score' => 'Both Teams to Score',
                'BTS' => 'Both Teams to Score',
                'Double Chance' => 'Double Chance',
                'Draw' => 'Draw',
            ];
            $dbCat = $catMap[$request->category] ?? null;
            if ($dbCat) {
                $query->whereHas('predictions', function ($q) use ($dbCat) {
                    $q->where('category', $dbCat);
                });
            }
        }

        $fixtures = $query->orderBy('match_date')->paginate(20);
        $fixturesByLeague = $fixtures->getCollection()->groupBy('league_name');

        // Get distinct leagues for the filter dropdown
        $leagues = Fixture::whereDate('match_date', today())
            ->whereNotNull('league_name')
            ->distinct()
            ->pluck('league_name')
            ->sort()
            ->values();

        return view('predictions.index', compact('fixturesByLeague', 'fixtures', 'leagues'));
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
        return $this->market('over_1_5');
    }

    public function over25()
    {
        return $this->market('over_2_5');
    }

    public function doubleChance()
    {
        return $this->market('double_chance');
    }

    public function bts()
    {
        return $this->market('btts');
    }

    public function draw()
    {
        return $this->market('draw');
    }

    public function correctScore()
    {
        return $this->market('correct_score');
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

    public function league(string $slug, Request $request)
    {
        $league = League::where('slug', $slug)->first();

        if (! $league) {
            abort(404);
        }

        $markets = PredictionCategory::where('enabled', true)->orderBy('sort_order')->get();

        $marketCode = $request->query('market');
        if ($marketCode && ! in_array($marketCode, $markets->pluck('code')->all(), true)) {
            $marketCode = null;
        }

        $dateRange = $request->query('date', 'all');
        if (! in_array($dateRange, ['all', 'today', 'tomorrow', '3days', '7days'], true)) {
            $dateRange = 'all';
        }

        $fixtures = $league->enabled
            ? $this->public->getLeaguePredictions($league, $marketCode, $dateRange === 'all' ? null : $dateRange, 20)
            : new LengthAwarePaginator([], 0, 20);

        return view('predictions.league', compact('league', 'fixtures', 'markets', 'marketCode', 'dateRange'));
    }

    public function fixture(string $leagueSlug, string $fixtureSlug)
    {
        $league = League::where('slug', $leagueSlug)->first();

        if (! $league) {
            abort(404);
        }

        $fixture = Fixture::where('slug', $fixtureSlug)
            ->where('league_id', $league->api_football_league_id)
            ->first();

        if (! $fixture) {
            abort(404);
        }

        $fixture = $this->public->getFixturePredictions($fixture);

        return view('predictions.fixture', compact('league', 'fixture'));
    }

    public function market(string $code)
    {
        $category = PredictionCategory::where('code', $code)->first();

        if (! $category) {
            abort(404);
        }

        $fixtures = $category->enabled
            ? $this->public->getMarketPredictions($code, 20)
            : new LengthAwarePaginator([], 0, 20);

        return view('predictions.market', compact('category', 'fixtures'));
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
