<?php

namespace App\Console\Commands;

use App\Models\BacktestPrediction;
use App\Models\BacktestRun;
use App\Models\PredictionModel;
use App\Services\Prediction\Calibration\WalkForwardCalibrator;
use Illuminate\Console\Command;

/**
 * Trains walk-forward per-market probability calibration on historical
 * resolved predictions and stores the parameters in the v1.1.0 candidate
 * model configuration. Never overwrites v1.0.0 and never activates v1.1.0.
 */
class CalibrateModel extends Command
{
    protected $signature = 'predictions:calibrate
                            {--train-fraction=0.7 : Fraction of chronological data used for training}';

    protected $description = 'Train walk-forward probability calibration and store it in the v1.1.0 candidate model';

    public function handle(WalkForwardCalibrator $calibrator): int
    {
        $trainFraction = (float) $this->option('train-fraction');

        $rows = $this->loadRows();

        if (empty($rows)) {
            $this->warn('No resolved predictions available to calibrate on.');
            $this->line('Run a backtest first (predictions:backtest) or resolve live results.');
            return 1;
        }

        $this->info('Fitting walk-forward calibration on '.count($rows).' resolved predictions...');

        $report = $calibrator->fitAndEvaluate($rows, $trainFraction);

        // Store calibration parameters in the v1.1.0 candidate model.
        $model = PredictionModel::where('version', 'v1.1.0')->first();

        if (! $model) {
            $this->error('v1.1.0 model not found. Run db:seed PredictionModelSeeder first.');
            return 1;
        }

        $configuration = $model->configuration ?? [];
        $configuration['calibration'] = array_map(
            fn ($m) => $m->parameters(),
            $report['models']
        );
        $configuration['calibration_meta'] = [
            'train_count' => $report['train_count'],
            'validation_count' => $report['validation_count'],
            'train_fraction' => $trainFraction,
            'calibrated_at' => now()->toDateTimeString(),
        ];

        $model->update(['configuration' => $configuration]);

        $this->line('');
        $this->table(
            ['Market', 'Train', 'Val', 'Raw Brier', 'Platt Brier', 'Iso Brier', 'Raw ECE', 'Platt ECE'],
            collect($report['per_market'])->map(function ($m, $code) {
                return [
                    $code,
                    $m['train_count'] ?? 0,
                    $m['validation_count'] ?? 0,
                    $this->fmt($m['raw_brier'] ?? null),
                    $this->fmt($m['platt_brier'] ?? null),
                    $this->fmt($m['isotonic_brier'] ?? null),
                    $this->fmt($m['raw_ece'] ?? null),
                    $this->fmt($m['platt_ece'] ?? null),
                ];
            })->values()->all(),
        );

        $this->line('');
        $this->info('Calibration stored in v1.1.0 (status: candidate). v1.0.0 is untouched.');
        $this->info('Activate v1.1.0 manually only after it demonstrates improvement on held-out data.');

        return 0;
    }

    /**
     * @return list<array{market_code:string,probability:float,result:string,t:string}>
     */
    protected function loadRows(): array
    {
        // Prefer the latest completed backtest run (cleanest walk-forward source
        // and avoids double-counting duplicate runs of the same fixtures).
        $run = BacktestRun::query()
            ->where('status', BacktestRun::STATUS_COMPLETED)
            ->latest('id')
            ->first();

        if ($run) {
            $rows = BacktestPrediction::query()
                ->where('backtest_run_id', $run->id)
                ->whereNotNull('result')
                ->whereNotNull('probability')
                ->select(['market_code', 'probability', 'result', 'predicted_at'])
                ->orderBy('predicted_at')
                ->get()
                ->map(fn ($p) => [
                    'market_code' => $p->market_code,
                    'probability' => (float) $p->probability,
                    'result' => $p->result,
                    't' => $p->predicted_at?->toDateTimeString() ?? '',
                ])
                ->all();

            if (! empty($rows)) {
                return $rows;
            }
        }

        // Fallback: live resolved predictions.
        return \App\Models\Prediction::query()
            ->whereNotNull('result')
            ->whereNotNull('probability')
            ->select(['market_code', 'probability', 'result', 'resolved_at'])
            ->orderBy('resolved_at')
            ->get()
            ->map(fn ($p) => [
                'market_code' => $p->market_code ?? '1x2',
                'probability' => (float) $p->probability,
                'result' => $p->result,
                't' => $p->resolved_at?->toDateTimeString() ?? '',
            ])
            ->all();
    }

    protected function fmt(mixed $v): string
    {
        return $v === null ? '—' : number_format((float) $v, 4, '.', '');
    }
}
