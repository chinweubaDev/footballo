<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeaturePredictionRequest;
use App\Http\Requests\LockPredictionRequest;
use App\Http\Requests\OverridePredictionRequest;
use App\Http\Requests\PublishPredictionRequest;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Services\Prediction\Admin\PredictionAdminService;
use App\Services\Prediction\Admin\PredictionOverrideService;
use App\Services\Prediction\Admin\PredictionPublishingService;
use Illuminate\Http\Request;

class PredictionAdminController extends Controller
{
    public function __construct(
        protected PredictionAdminService $admin,
        protected PredictionOverrideService $overrides,
        protected PredictionPublishingService $publishing,
    ) {
    }

    public function dashboard()
    {
        return view('admin.predictions.dashboard', [
            'stats' => $this->admin->dashboardStats(),
            'latestPredictions' => $this->admin->latestPredictions(),
            'recentOverrides' => $this->admin->recentOverrides(),
            'activeModel' => $this->admin->activeModel(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Prediction::query()
            ->with(['fixture', 'league', 'model'])
            ->orderByDesc('created_at');

        if ($request->filled('league')) {
            $query->where('league_id', (int) $request->input('league'));
        }

        if ($request->filled('market')) {
            $query->where('market_code', $request->input('market'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('model_version')) {
            $query->where('model_version', $request->input('model_version'));
        }

        if ($request->filled('min_confidence')) {
            $query->where('confidence', '>=', (int) $request->input('min_confidence'));
        }

        if ($request->filled('min_probability')) {
            $query->where('probability', '>=', (float) $request->input('min_probability'));
        }

        if ($request->filled('featured')) {
            $query->where('featured', true);
        }

        if ($request->filled('overridden')) {
            $query->whereNotNull('admin_selection');
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->whereHas('fixture', function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('api_fixture_id', 'like', "%{$search}%");
            });
        }

        $this->applyDateFilter($query, $request);

        $predictions = $query->paginate(25)->withQueryString();

        return view('admin.predictions.list', [
            'predictions' => $predictions,
            'leagues' => League::query()->orderBy('name')->get(['id', 'name', 'api_football_league_id']),
            'markets' => PredictionCategory::query()->orderBy('sort_order')->get(['id', 'name', 'code']),
            'modelVersions' => Prediction::query()->distinct()->orderBy('model_version')->pluck('model_version'),
        ]);
    }

    public function show(Prediction $prediction)
    {
        $prediction->load(['fixture', 'league', 'model', 'overrides.admin', 'features']);

        return view('admin.predictions.show', compact('prediction'));
    }

    public function override(Prediction $prediction, OverridePredictionRequest $request)
    {
        try {
            $this->overrides->override(
                $prediction,
                $request->validated('selection'),
                $request->validated('probability') !== null ? (float) $request->validated('probability') : null,
                $request->validated('reason'),
                $request->user(),
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Prediction overridden.');
    }

    public function revert(Prediction $prediction)
    {
        $this->authorize('revert', $prediction);
        $this->overrides->revert($prediction, request()->user());

        return back()->with('success', 'Reverted to AI prediction.');
    }

    public function lock(Prediction $prediction, LockPredictionRequest $request)
    {
        $this->admin->lock($prediction, $request->user());

        return back()->with('success', 'Prediction locked.');
    }

    public function unlock(Prediction $prediction)
    {
        $this->authorize('unlock', $prediction);
        $this->admin->unlock($prediction, request()->user());

        return back()->with('success', 'Prediction unlocked.');
    }

    public function publish(Prediction $prediction, PublishPredictionRequest $request)
    {
        try {
            $this->publishing->publish($prediction, $request->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Prediction published.');
    }

    public function unpublish(Prediction $prediction)
    {
        $this->authorize('publish', $prediction);
        $this->publishing->unpublish($prediction, request()->user());

        return back()->with('success', 'Prediction unpublished.');
    }

    public function feature(Prediction $prediction, FeaturePredictionRequest $request)
    {
        try {
            $this->admin->feature($prediction, $request->validated(), $request->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Prediction featured.');
    }

    public function unfeature(Prediction $prediction)
    {
        $this->authorize('feature', $prediction);
        $this->admin->unfeature($prediction, request()->user());

        return back()->with('success', 'Prediction unfeatured.');
    }

    protected function applyDateFilter($query, Request $request): void
    {
        $date = $request->input('date');

        $query->whereHas('fixture', function ($q) use ($date, $request) {
            switch ($date) {
                case 'today':
                    $q->whereDate('match_date', today());
                    break;
                case 'tomorrow':
                    $q->whereDate('match_date', today()->addDay());
                    break;
                case '3days':
                    $q->whereBetween('match_date', [today(), today()->addDays(3)]);
                    break;
                case '7days':
                    $q->whereBetween('match_date', [today(), today()->addDays(7)]);
                    break;
                case 'custom':
                    if ($request->filled('from')) {
                        $q->whereDate('match_date', '>=', $request->input('from'));
                    }
                    if ($request->filled('to')) {
                        $q->whereDate('match_date', '<=', $request->input('to'));
                    }
                    break;
            }
        });
    }
}
