<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;

class PredictionPerformanceController extends Controller
{
    public function __construct(
        protected PerformanceAnalyticsService $performance,
    ) {
    }

    public function index()
    {
        $data = $this->performance->dashboard();

        return view('admin.predictions.performance', $data);
    }
}
