<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionCategory;
use App\Services\Prediction\Admin\GateReportService;
use App\Services\Prediction\Admin\MarketGateService;
use Illuminate\Http\Request;

class PredictionGateController extends Controller
{
    public function __construct(
        protected GateReportService $report,
        protected MarketGateService $gates,
    ) {
    }

    public function index()
    {
        return view('admin.predictions.gates', [
            'report' => $this->report->report(),
        ]);
    }

    public function approve(PredictionCategory $category, Request $request)
    {
        $data = $request->validate([
            'min_probability' => ['required', 'integer', 'min:0', 'max:100'],
            'min_confidence' => ['required', 'integer', 'min:0', 'max:100'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->gates->approve(
            $category,
            $request->user(),
            (int) $data['min_probability'],
            (int) $data['min_confidence'],
            $data['reason'] ?? null,
        );

        return back()->with('success', "{$category->name} gate approved (prob ≥ {$data['min_probability']}, conf ≥ {$data['min_confidence']}).");
    }

    public function reject(PredictionCategory $category, Request $request)
    {
        $data = $request->validate(['reason' => ['nullable', 'string']]);

        $this->gates->reject($category, $request->user(), $data['reason'] ?? null);

        return back()->with('success', "{$category->name} recommendation rejected.");
    }
}
