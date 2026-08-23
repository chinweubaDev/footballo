<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeagueMarketGateRequest;
use App\Models\League;
use App\Services\Prediction\Admin\LeagueMarketGateService;
use Illuminate\Http\Request;

/**
 * Phase 1I — league x market publication gate matrix.
 */
class LeagueMarketGateController extends Controller
{
    public function __construct(protected LeagueMarketGateService $service)
    {
    }

    public function index()
    {
        return view('admin.predictions.settings.matrix', [
            'leagues' => $this->service->leagues(),
            'markets' => $this->service->markets(),
            'matrix' => $this->service->matrix(),
        ]);
    }

    public function update(UpdateLeagueMarketGateRequest $request, League $league, string $marketCode)
    {
        $this->service->update(
            $league,
            $marketCode,
            $request->boolean('enabled'),
            $request->filled('min_probability') ? (int) $request->input('min_probability') : null,
            $request->filled('min_confidence') ? (int) $request->input('min_confidence') : null,
            $request->user(),
        );

        return back()->with('success', "{$league->name} × {$marketCode} gate updated.");
    }
}
