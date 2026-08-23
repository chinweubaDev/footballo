<?php

namespace App\Services\Prediction\Admin;

use App\Models\Prediction;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1J — automatic prediction locking.
 *
 * Locks predictions approaching kickoff so that routine generation can no
 * longer modify them. Admin overrides remain a separate, audited path.
 */
class PredictionLockService
{
    /**
     * Lock every prediction whose fixture kicks off within the window.
     *
     * @return int number of predictions locked
     */
    public function lockDuePredictions(int $windowMinutes = 30): int
    {
        $locked = 0;

        Prediction::query()
            ->whereNull('locked_at')
            ->whereIn('status', ['published', 'generated', 'shadow'])
            ->whereHas('fixture', function ($q) use ($windowMinutes) {
                $q->whereIn('status', ['NS', 'TBD', 'PST'])
                    ->whereNotNull('match_date')
                    ->whereBetween('match_date', [now(), now()->addMinutes($windowMinutes)]);
            })
            ->select(['id'])
            ->chunkById(200, function ($rows) use (&$locked) {
                foreach ($rows as $row) {
                    // Guard with an atomic conditional update to remain
                    // idempotent under concurrent runs.
                    $updated = Prediction::where('id', $row->id)
                        ->whereNull('locked_at')
                        ->update(['locked_at' => now()]);

                    if ($updated) {
                        $locked++;
                    }
                }
            });

        return $locked;
    }
}
