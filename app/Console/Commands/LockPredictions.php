<?php

namespace App\Console\Commands;

use App\Services\Prediction\Admin\PredictionLockService;
use Illuminate\Console\Command;

/**
 * Phase 1J — lock predictions approaching kickoff.
 *
 *   php artisan predictions:lock --window=30
 */
class LockPredictions extends Command
{
    protected $signature = 'predictions:lock
                            {--window=30 : Minutes before kickoff to lock}';

    protected $description = 'Lock predictions whose fixtures kick off within the configured window';

    public function handle(PredictionLockService $locker): int
    {
        $window = (int) $this->option('window');
        $locked = $locker->lockDuePredictions($window);

        $this->info("Locked {$locked} prediction(s) (window: {$window} minutes).");

        return 0;
    }
}
