<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaguePredictionSettingsRequest;
use App\Models\League;
use App\Services\Prediction\Admin\LeaguePredictionSettingsService;

class PredictionLeagueController extends Controller
{
    public function __construct(protected LeaguePredictionSettingsService $service)
    {
    }

    public function index()
    {
        $leagues = League::query()
            ->withCount(['fixtures' => function ($query) {
                $query->where('status', 'NS')->whereDate('match_date', '>=', today());
            }])
            ->withCount('predictions')
            ->orderBy('priority', 'asc')
            ->get();

        return view('admin.predictions.leagues', compact('leagues'));
    }

    public function toggleEnabled(League $league)
    {
        $this->service->toggleEnabled($league, request()->user());

        return back()->with('success', 'League updated.');
    }

    public function updateSettings(League $league, UpdateLeaguePredictionSettingsRequest $request)
    {
        $this->service->update($league, $request->validated(), $request->user());

        return back()->with('success', 'League settings saved.');
    }
}
