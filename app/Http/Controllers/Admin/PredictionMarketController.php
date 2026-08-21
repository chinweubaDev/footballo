<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePredictionCategoryRequest;
use App\Models\PredictionCategory;
use App\Services\Prediction\Admin\MarketPredictionSettingsService;

class PredictionMarketController extends Controller
{
    public function __construct(protected MarketPredictionSettingsService $service)
    {
    }

    public function index()
    {
        $markets = PredictionCategory::query()->orderBy('sort_order')->get();

        return view('admin.predictions.markets', compact('markets'));
    }

    public function toggleEnabled(PredictionCategory $category)
    {
        $this->service->toggleEnabled($category, request()->user());

        return back()->with('success', 'Market updated.');
    }

    public function updateSettings(PredictionCategory $category, UpdatePredictionCategoryRequest $request)
    {
        $this->service->update($category, $request->validated(), $request->user());

        return back()->with('success', 'Market settings saved.');
    }
}
