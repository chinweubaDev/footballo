<?php

namespace App\Console\Commands;

use App\Services\Prediction\Evaluation\PerformanceAnalyticsService;
use App\Services\Prediction\Evaluation\PredictionResultService;
use Illuminate\Console\Command;

/**
 * Resolves completed fixtures' predictions against final results.
 * Idempotent: running it repeatedly never duplicates results.
 */
class ResolvePredictionResults extends Command
{
    protected $signature = 'predictions:resolve-results {--limit= : Maximum number of fixtures to process}';

    protected $description = 'Resolve predictions for completed matches (WON/LOST/VOID) and refresh performance caches';

    public function handle(PredictionResultService $resolver, PerformanceAnalyticsService $performance): int
    {
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Resolving completed fixture predictions...');

        $result = $resolver->resolveCompletedFixtures($limit);

        $this->line("   Fixtures processed: {$result['fixtures']}");
        $this->line("   Predictions resolved: {$result['predictions']}");

        // Invalidate performance caches so the dashboard reflects new results.
        $performance->flush();

        $this->info('Done.');

        return 0;
    }
}
