<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionModel;
use App\Services\Prediction\Calibration\CalibrationReportService;
use App\Services\Prediction\Calibration\ModelComparisonService;
use App\Services\Prediction\Evaluation\LiveValidationService;
use App\Services\Prediction\Validation\ModelLifecycleService;
use App\Services\Prediction\Validation\ShadowComparisonService;
use App\Services\Prediction\Validation\SignificanceService;
use App\Services\Prediction\Validation\ValidationReportService;
use Illuminate\Http\Request;

class PredictionModelController extends Controller
{
    public function __construct(
        protected ModelComparisonService $comparison,
        protected CalibrationReportService $reports,
        protected ModelLifecycleService $lifecycle,
        protected ShadowComparisonService $shadow,
        protected ValidationReportService $validation,
        protected SignificanceService $significance,
        protected LiveValidationService $live,
    ) {
    }

    public function index()
    {
        return view('admin.predictions.models.index', [
            'models' => $this->comparison->models(),
            'versions' => $this->comparison->versions(),
            'live' => $this->live->summary(),
            'minimumSample' => (int) config('evaluation.minimum_sample_size', 100),
        ]);
    }

    public function compare(Request $request)
    {
        $versions = array_keys($this->comparison->versions());
        $a = $request->query('a', $versions[0] ?? null);
        $b = $request->query('b', $versions[1] ?? null);

        $dataA = $a ? $this->comparison->summarizeVersion($a) : null;
        $dataB = $b ? $this->comparison->summarizeVersion($b) : null;

        return view('admin.predictions.models.compare', [
            'versions' => $versions,
            'a' => $a,
            'b' => $b,
            'dataA' => $dataA,
            'dataB' => $dataB,
            'significance' => ($a && $b) ? $this->significance->compareVersions($a, $b) : null,
            'minimumSample' => (int) config('evaluation.minimum_sample_size', 100),
        ]);
    }

    public function dataQuality()
    {
        return view('admin.predictions.models.data-quality', [
            'report' => $this->reports->dataQualityReport(),
        ]);
    }

    public function shadow()
    {
        return view('admin.predictions.models.shadow', [
            'data' => $this->shadow->dashboard(),
        ]);
    }

    public function validation()
    {
        return view('admin.predictions.models.validation', [
            'report' => $this->validation->report(),
        ]);
    }

    public function approve(PredictionModel $model, Request $request)
    {
        $this->lifecycle->approve($model, $request->user(), $request->input('reason'));

        return back()->with('success', "{$model->version} approved.");
    }

    public function reject(PredictionModel $model, Request $request)
    {
        $this->lifecycle->reject($model, $request->user(), $request->input('reason'));

        return back()->with('success', "{$model->version} rejected.");
    }

    public function activate(PredictionModel $model, Request $request)
    {
        try {
            $this->lifecycle->activate($model, $request->user(), $request->input('reason'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$model->version} is now ACTIVE.");
    }

    public function retire(PredictionModel $model, Request $request)
    {
        $this->lifecycle->retire($model, $request->user(), $request->input('reason'));

        return back()->with('success', "{$model->version} retired.");
    }

    public function rollback(PredictionModel $model, Request $request)
    {
        $this->lifecycle->rollback($model, $request->user(), $request->input('reason'));

        return back()->with('success', "Production rolled back to {$model->version}.");
    }
}
