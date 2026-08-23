<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Models\PredictionModel;
use App\Models\SystemEvent;
use App\Services\Prediction\Evaluation\LiveValidationService;
use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Phase 1M — live validation, model comparison & evidence analysis.
 *
 * Read-only measurement dashboards + the audit export. Nothing here tunes the
 * model, changes gates, or promotes a shadow model.
 */
class LiveValidationController extends Controller
{
    public function __construct(
        protected LiveValidationService $live,
        protected PerformanceAnalyticsService $performance,
    ) {
    }

    /**
     * /admin/predictions/live-validation
     */
    public function summary()
    {
        return view('admin.predictions.live-validation', [
            'summary' => $this->live->summary(),
            'paired' => $this->live->pairedComparison(),
            'agreement' => $this->live->modelAgreement(),
            'gates' => $this->live->gateAnalysis(),
            'surePicks' => $this->live->surePicksEvaluation(),
            'mostFeatured' => $this->live->mostFeaturedEvaluation(),
            'models' => PredictionModel::query()->orderBy('version')->get(),
        ]);
    }

    /**
     * /admin/predictions/live-validation/report — consolidated daily report.
     */
    public function report()
    {
        return view('admin.predictions.live-validation-report', [
            'date' => now()->toDateString(),
            'summary' => $this->live->summary(),
            'paired' => $this->live->pairedComparison(),
            'agreement' => $this->live->modelAgreement(),
            'gates' => $this->live->gateAnalysis(),
            'surePicks' => $this->live->surePicksEvaluation(),
            'mostFeatured' => $this->live->mostFeaturedEvaluation(),
            'marketByModel' => $this->live->marketPerformanceByModel(),
            'leagueByModel' => $this->live->leaguePerformanceByModel(),
            'matrixByModel' => $this->live->leagueMarketMatrixByModel(),
            'versions' => $this->live->versions(),
            'apiHealth' => $this->apiHealth(),
            'queueHealth' => $this->queueHealth(),
            'settlementHealth' => $this->settlementHealth(),
        ]);
    }

    protected function apiHealth(): array
    {
        return [
            'requests_today' => ApiRequestLog::whereDate('created_at', today())->count(),
            'successful_today' => ApiRequestLog::whereDate('created_at', today())->where('successful', true)->count(),
            'failed_today' => ApiRequestLog::whereDate('created_at', today())->where('successful', false)->count(),
            'rate_limited_today' => ApiRequestLog::whereDate('created_at', today())->where('is_rate_limited', true)->count(),
            'avg_duration_ms' => (int) round(ApiRequestLog::whereDate('created_at', today())->avg('duration_ms') ?? 0),
        ];
    }

    protected function queueHealth(): array
    {
        $config = config('services.queue_health');
        $now = now()->timestamp;

        $pending = DB::table('jobs')->whereNull('reserved_at')->where('available_at', '<=', $now)->count();
        $processing = DB::table('jobs')->whereNotNull('reserved_at')->count();
        $failed = DB::table('failed_jobs')->count();

        $recentCritical = SystemEvent::where('severity', SystemEvent::SEVERITY_ERROR)
            ->where('created_at', '>=', now()->subMinutes((int) $config['critical_window_minutes']))
            ->exists();

        $status = $recentCritical
            ? 'FAILED'
            : ($failed > 0 || $pending >= (int) $config['pending_warning_threshold'] ? 'WARNING' : 'HEALTHY');

        return ['pending' => $pending, 'processing' => $processing, 'failed' => $failed, 'status' => $status];
    }

    protected function settlementHealth(): array
    {
        $today = today();

        return [
            'settled' => \App\Models\Prediction::whereNotNull('settled_at')->count(),
            'pending_review' => \App\Models\Prediction::where('settlement_status', 'pending_review')->count(),
            'provenance_invalid' => \App\Models\Prediction::where('provenance_status', 'invalid')->count(),
            'provenance_uncertain' => \App\Models\Prediction::where('provenance_status', 'provenance_uncertain')->count(),
            'settlement_failures_today' => SystemEvent::where('type', 'settlement_failure')->whereDate('created_at', $today)->count(),
        ];
    }

    /**
     * /admin/predictions/performance/markets
     */
    public function markets()
    {
        return view('admin.predictions.performance.markets', [
            'byModel' => $this->live->marketPerformanceByModel(),
            'versions' => $this->live->versions(),
        ]);
    }

    /**
     * /admin/predictions/performance/leagues
     */
    public function leagues()
    {
        return view('admin.predictions.performance.leagues', [
            'byModel' => $this->live->leaguePerformanceByModel(),
            'versions' => $this->live->versions(),
        ]);
    }

    /**
     * /admin/predictions/performance/matrix
     */
    public function matrix()
    {
        return view('admin.predictions.performance.matrix', [
            'byModel' => $this->live->leagueMarketMatrixByModel(),
            'versions' => $this->live->versions(),
        ]);
    }

    /**
     * /admin/predictions/performance/export — live-validation audit CSV.
     */
    public function export(): StreamedResponse
    {
        $models = $this->live->exportModels();

        $headers = [
            'fixture_id',
            'kickoff',
            'league',
            'home_team',
            'away_team',
            'market',
            'selection',
            'model_version',
            'raw_probability',
            'calibrated_probability',
            'probability',
            'confidence',
            'status',
            'locked_at',
            'actual_score',
            'result',
            'model_result',
            'override_result',
            'public_result',
            'settlement_result',
            'provenance_status',
            'prediction_generated_at',
            'feature_data_timestamp',
            'settled_at',
        ];

        return response()->streamDownload(function () use ($models, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            foreach ($models as $p) {
                fputcsv($out, [
                    $p->fixture_id,
                    $p->fixture?->match_date?->toDateTimeString() ?? '',
                    $p->league?->name ?? '',
                    $p->fixture?->home_team ?? '',
                    $p->fixture?->away_team ?? '',
                    $p->market_code ?? '',
                    $p->selection ?? '',
                    $p->model_version ?? '',
                    $p->raw_probability !== null ? (string) $p->raw_probability : '',
                    $p->calibrated_probability !== null ? (string) $p->calibrated_probability : '',
                    $p->probability !== null ? (string) $p->probability : '',
                    $p->confidence ?? '',
                    $p->status ?? '',
                    $p->locked_at?->toDateTimeString() ?? '',
                    $p->actual_score ?? '',
                    $p->result ?? '',
                    $p->model_result ?? '',
                    $p->override_result ?? '',
                    $p->public_result ?? '',
                    $p->settlement_result ?? '',
                    $p->provenance_status ?? '',
                    $p->prediction_generated_at?->toDateTimeString() ?? '',
                    $p->feature_data_timestamp?->toDateTimeString() ?? '',
                    $p->settled_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($out);
        }, 'live-validation-export.csv', ['Content-Type' => 'text/csv']);
    }
}
