<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBacktestRequest;
use App\Jobs\RunBacktestJob;
use App\Models\BacktestRun;
use App\Models\League;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BacktestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $runs = BacktestRun::query()
            ->with(['league', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.predictions.backtesting.index', compact('runs'));
    }

    public function create()
    {
        return view('admin.predictions.backtesting.create', [
            'leagues' => League::query()->orderBy('name')->get(['id', 'name', 'api_football_league_id']),
            'markets' => PredictionCategory::query()->enabled()->orderBy('sort_order')->get(['id', 'name', 'code']),
            'models' => PredictionModel::query()->orderBy('version')->get(['id', 'name', 'version', 'active']),
            'activeVersion' => config('prediction.model_version', 'v1.0.0'),
        ]);
    }

    public function store(CreateBacktestRequest $request)
    {
        $data = $request->normalized();

        $run = BacktestRun::create(array_merge($data, [
            'name' => 'Backtest '.$data['model_version'].' '.now()->toDateTimeString(),
            'config_snapshot' => $this->configSnapshot($data),
            'status' => BacktestRun::STATUS_QUEUED,
            'created_by' => $request->user()->id,
        ]));

        RunBacktestJob::dispatch($run->id);

        return redirect()
            ->route('admin.predictions.backtesting.show', $run)
            ->with('success', 'Backtest queued. It will start shortly.');
    }

    public function show(BacktestRun $backtest)
    {
        $this->authorize('view', $backtest);

        $backtest->load(['league', 'creator', 'predictions']);

        return view('admin.predictions.backtesting.show', [
            'run' => $backtest,
            'metrics' => $backtest->metrics ?? [],
            'minimumSample' => (int) config('evaluation.minimum_sample_size', 100),
        ]);
    }

    public function cancel(BacktestRun $backtest)
    {
        $this->authorize('cancel', $backtest);

        if (! $backtest->is_finished && $backtest->status !== BacktestRun::STATUS_QUEUED) {
            $backtest->update(['status' => BacktestRun::STATUS_CANCELLED, 'completed_at' => now()]);
        }

        return back()->with('success', 'Backtest cancellation requested.');
    }

    public function archive(BacktestRun $backtest)
    {
        $this->authorize('archive', $backtest);

        // Prefer archive over hard delete: soft-flag the run.
        $backtest->update(['status' => BacktestRun::STATUS_CANCELLED]);

        return back()->with('success', 'Backtest archived.');
    }

    public function export(BacktestRun $backtest): StreamedResponse
    {
        $this->authorize('view', $backtest);

        return response()->streamDownload(function () use ($backtest) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'fixture_id', 'market_code', 'selection', 'probability',
                'confidence', 'model_version', 'data_quality_score', 'status',
                'result', 'actual_score',
            ]);

            $backtest->predictions()
                ->select([
                    'fixture_id', 'market_code', 'selection', 'probability',
                    'confidence', 'model_version', 'data_quality_score', 'status',
                    'result', 'actual_score',
                ])
                ->chunkById(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->fixture_id,
                            $row->market_code,
                            $row->selection,
                            $row->probability,
                            $row->confidence,
                            $row->model_version,
                            $row->data_quality_score,
                            $row->status,
                            $row->result,
                            $row->actual_score,
                        ]);
                    }
                });

            fclose($handle);
        }, "backtest-{$backtest->id}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * A full configuration snapshot so a completed backtest is reproducible
     * even if the active model configuration changes later.
     */
    protected function configSnapshot(array $data): array
    {
        return array_merge([
            'created_at' => now()->toDateTimeString(),
            'model_version' => $data['model_version'],
            'min_confidence' => $data['min_confidence'],
            'min_probability' => $data['min_probability'],
            'markets' => $data['markets'],
            'ensemble' => config('prediction.ensemble'),
            'poisson' => config('prediction.poisson'),
            'home_advantage' => config('prediction.home_advantage'),
            'confidence' => config('prediction.confidence'),
            'no_bet' => config('prediction.no_bet'),
        ], config('prediction'));
    }
}
