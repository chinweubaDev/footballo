<?php

namespace App\Console\Commands;

use App\Services\Prediction\Validation\ValidationMatrixService;
use Illuminate\Console\Command;

/**
 * Phase 1G.2 — print the full League x Market x Model validation matrix.
 *
 *   php artisan predictions:matrix --season=2025
 *   php artisan predictions:matrix --season=2025 --model-version=v1.0.0 --json
 */
class ValidationMatrix extends Command
{
    protected $signature = 'predictions:matrix
                            {--season= : Season year}
                            {--model-version= : Model version (omit for all)}';

    protected $description = 'Print the full League x Market x Model validation matrix';

    public function handle(ValidationMatrixService $service): int
    {
        $season = $this->option('season') ? (int) $this->option('season') : null;
        $modelVersion = $this->option('model-version') ?: null;

        $report = $service->matrix($season, $modelVersion);

        $this->line('');
        $this->info('PHASE 1G.2 VALIDATION MATRIX');
        $this->line('Season: '.($season ?? 'all'));
        $this->line('Models: '.implode(', ', $report['versions']));

        foreach ($report['models'] as $version => $model) {
            $this->line('');
            $this->info("=== {$version} ===");

            if (! ($model['available'] ?? false)) {
                $this->warn('NOT AVAILABLE — no completed backtest runs.');
                $idx = array_search($version, $report['versions']);
                $note = ($idx !== false && isset($report['model_comparison'][$idx]['note'])) ? $report['model_comparison'][$idx]['note'] : '';
                $this->line($note);
                continue;
            }

            $this->printMatrix($model['leagues']);
            $this->printMarketSummary($model['market_summary']);
            $this->printLeagueSummary($model['league_summary']);
            $this->printRanking($model['ranking']);
            $this->printCandidates($model['publication_candidates']);
        }

        $this->line('');
        $this->info('Model comparison');
        $headers = ['Version', 'Available', 'Leagues', 'Pooled acc', 'Mean Brier', 'Mean logloss', 'Mean ECE'];
        $rows = array_map(fn ($c) => [
            $c['version'],
            $c['available'] ? 'yes' : 'NO',
            $c['leagues'],
            $this->fmt($c['pooled_accuracy'], '%'),
            $this->fmt($c['mean_brier'], '', 4),
            $this->fmt($c['mean_log_loss'], '', 4),
            $this->fmt($c['mean_calibration_error'], ''),
        ], $report['model_comparison']);
        $this->table($headers, $rows);

        $this->line('');
        $this->info('Strong markets');
        $this->printMarketList($report['strong_markets']);

        $this->line('');
        $this->info('Weak markets');
        $this->printMarketList($report['weak_markets']);

        $this->line('');
        $this->info('PHASE 1G.2 COMPLETE — READY FOR PHASE 1H');

        return 0;
    }

    protected function printMatrix(array $leagues): void
    {
        $headers = array_merge(['Market'], array_map(fn ($l) => $l['league_name'], $leagues));

        $rows = [];

        foreach (ValidationMatrixService::MARKETS as $code) {
            $row = [ValidationMatrixService::MARKET_LABELS[$code] ?? $code];

            foreach ($leagues as $league) {
                $m = $league['markets'][$code] ?? null;

                if ($m && $m['n'] > 0) {
                    $row[] = $this->fmt($m['accuracy'], '%')." (n={$m['n']})";
                } else {
                    $row[] = '—';
                }
            }

            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }

    protected function printMarketSummary(array $summary): void
    {
        $headers = ['Market', 'Leagues', 'n', 'Mean', 'Median', 'Std', 'Min', 'Max', 'Brier'];

        $rows = array_map(fn ($s) => [
            $s['market_label'],
            $s['leagues_evaluated'],
            $s['total_n'],
            $this->fmt($s['mean_accuracy'], '%'),
            $this->fmt($s['median_accuracy'], '%'),
            $this->fmt($s['std_accuracy'], ''),
            $this->fmt($s['min_accuracy'], '%'),
            $this->fmt($s['max_accuracy'], '%'),
            $this->fmt($s['mean_brier'], '', 4),
        ], $summary);

        $this->table($headers, $rows);
    }

    protected function printLeagueSummary(array $summary): void
    {
        $headers = ['League', 'Mean acc', 'Median acc', 'Std', 'Mean Brier'];

        $rows = array_map(fn ($l) => [
            $l['league_name'],
            $this->fmt($l['mean_accuracy'], '%'),
            $this->fmt($l['median_accuracy'], '%'),
            $this->fmt($l['std_accuracy'], ''),
            $this->fmt($l['mean_brier'], '', 4),
        ], $summary);

        $this->table($headers, $rows);
    }

    protected function printRanking(array $ranking): void
    {
        $headers = ['#', 'League', 'Market', 'n', 'Acc', 'Cov', 'Brier', 'Cal', 'Score', 'Sample'];

        $rows = array_map(fn ($c) => [
            $c['rank'],
            $c['league'],
            $c['market_label'],
            $c['n'],
            $this->fmt($c['accuracy'], '%'),
            $this->fmt($c['coverage'], '%'),
            $this->fmt($c['brier'], '', 4),
            $this->fmt($c['calibration'], ''),
            $this->fmt($c['score'], ''),
            $c['sample_status'],
        ], $ranking);

        $this->table($headers, $rows);
    }

    protected function printCandidates(array $candidates): void
    {
        $headers = ['League', 'Market', 'Acc', 'Cov', 'Brier', 'Cal', 'Gate 70/75', 'Rec gate', 'n', 'Status'];

        $rows = array_map(fn ($c) => [
            $c['league'],
            $c['market_label'],
            $this->fmt($c['accuracy'], '%'),
            $this->fmt($c['coverage'], '%'),
            $this->fmt($c['brier'], '', 4),
            $this->fmt($c['calibration'], ''),
            $this->fmt($c['gate_accuracy'], '%').' (n='.$c['gate_n'].')',
            isset($c['recommended_gate_n'])
                ? ($c['recommended_gate'].' (n='.$c['recommended_gate_n'].', '.$this->fmt($c['recommended_gate_accuracy'], '%').')')
                : ($c['recommended_gate'] ?? '—'),
            $c['n'],
            $c['status'],
        ], $candidates);

        $this->table($headers, $rows);
    }

    protected function printMarketList(array $markets): void
    {
        $headers = ['Market', 'Mean acc', 'Std', 'Brier', 'Leagues'];

        $rows = array_map(fn ($m) => [
            $m['market_label'],
            $this->fmt($m['mean_accuracy'], '%'),
            $this->fmt($m['std_accuracy'], ''),
            $this->fmt($m['mean_brier'], '', 4),
            $m['leagues_evaluated'],
        ], $markets);

        $this->table($headers, $rows);
    }

    protected function fmt($value, string $suffix = '', int $decimals = 2): string
    {
        return $value === null ? '—' : number_format((float) $value, $decimals).$suffix;
    }
}
