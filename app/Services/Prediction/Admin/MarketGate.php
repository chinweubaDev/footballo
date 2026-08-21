<?php

namespace App\Services\Prediction\Admin;

/**
 * Pure publication-gate decision (Phase 1G.1 §17).
 *
 * A prediction may only be bet when it passes every configured condition:
 * probability, confidence and data quality. This object contains no I/O so it
 * is trivial to test and to reuse in the prediction engine and in backtests.
 */
class MarketGate
{
    public const BET = 'bet';
    public const NO_BET = 'no_bet';

    public function decide(
        float $probability,
        int $confidence,
        int $dataQuality,
        int $minProbability,
        int $minConfidence,
        int $minDataQuality,
    ): string {
        if ($probability < $minProbability
            || $confidence < $minConfidence
            || $dataQuality < $minDataQuality) {
            return self::NO_BET;
        }

        return self::BET;
    }
}
