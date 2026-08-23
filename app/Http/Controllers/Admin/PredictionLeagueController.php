<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaguePredictionSettingsRequest;
use App\Models\League;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\Prediction\Admin\LeaguePredictionSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PredictionLeagueController extends Controller
{
    public function __construct(
        protected LeaguePredictionSettingsService $service,
        protected ApiFootballServiceEnhanced $api,
    ) {
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

    /**
     * Browse the full league catalog by country, plus optional live API search.
     */
    public function discover(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $catalog = config('leagues_catalog', []);

        $apiResults = [];
        $apiError = null;

        if ($search !== '') {
            $data = $this->api->getLeagues($search);
            $apiResults = is_array($data) ? ($data['response'] ?? []) : [];
            $errors = is_array($data) ? ($data['errors'] ?? []) : [];

            if ($errors !== [] && count($apiResults) === 0) {
                $apiError = collect($errors)->values()->first() ?: 'The live league search is currently unavailable.';
            }
        }

        $importedIds = League::query()->pluck('api_football_league_id')->all();

        $season = (int) config('prediction.default_season', 2025);

        return view('admin.predictions.leagues.discover', compact('catalog', 'apiResults', 'apiError', 'importedIds', 'search', 'season'));
    }

    /**
     * Import + enable a league discovered from the catalog or API-Football.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'api_football_league_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', 'integer'],
        ]);

        $logo = $validated['logo'] ?: 'https://media.api-sports.io/football/leagues/'.$validated['api_football_league_id'].'.png';

        $slug = Str::slug($validated['name']);

        if (League::query()->where('slug', $slug)->where('api_football_league_id', '!=', $validated['api_football_league_id'])->exists()) {
            $slug = $slug.'-'.$validated['api_football_league_id'];
        }

        League::updateOrCreate(
            ['api_football_league_id' => $validated['api_football_league_id']],
            [
                'name' => $validated['name'],
                'slug' => $slug,
                'country' => $validated['country'] ?? '',
                'logo' => $logo,
                'season' => $validated['season'] ?? (int) config('prediction.default_season', 2025),
                'enabled' => true,
                'prediction_enabled' => true,
                'homepage_enabled' => false,
                'priority' => 99,
                'prediction_min_confidence' => 75,
                'auto_publish' => true,
            ],
        );

        return back()->with('success', "\"{$validated['name']}\" added and enabled.");
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
