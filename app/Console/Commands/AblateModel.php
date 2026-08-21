<?php

namespace App\Console\Commands;

use App\Services\Prediction\Validation\AblationService;
use Illuminate\Console\Command;

/**
 * Runs ablation validation (Phase 1G §36): the full model against the model
 * with each feature group removed. Every ablation is stored as a BacktestRun
 * for reproducibility.
 */
class AblateModel extends Command
{
    protected $signature = 'predictions:ablate
                            {--league= : API-Football league id (default: all)}';

    protected $description = 'Run feature-ablation validation and compare Accuracy / Brier / Calibration';

    public function handle(AblationService $ablation): int
    {
        $leagueId = $this->option('league') ? (int) $this->option('league') : null;
        $season = config('prediction.default_season', 2025);
        $modelVersion = config('prediction.model_version', 'v1.0.0');

        $this->info("Running ablation validation for {$modelVersion} (season {$season}).");
        $this->line('This can take a while for large datasets.');

        $report = $ablation->run($leagueId, $season, $modelVersion);

        $rows = [];

        foreach ($report['results'] as $key => $r) {
            $rows[] = [
                $r['name'],
                $r['resolved'],
                $r['accuracy'] === null ? '—' : number_format($r['accuracy'], 2).'%',
                $r['brier_score'] === null ? '—' : number_format($r['brier_score'], 4),
                $r['calibration_error'] === null ? '—' : number_format($r['calibration_error'], 2),
                $r['coverage'] === null ? '—' : number_format($r['coverage'], 2).'%',
            ];
        }

        $this->table(
            ['Variant', 'n', 'Accuracy', 'Brier', 'Calibration (ECE)', 'Coverage'],
            $rows,
        );

        $this->line('');
        $this->info('Interpretation: a large drop in accuracy/Brier when a feature is removed means that feature matters out-of-sample.');

        return 0;
    }
}
