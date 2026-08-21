<?php

namespace App\Services\Prediction\Validation;

/**
 * Maps measured performance into a stable human label (Phase 1G §25/26).
 *
 * Labels are DERIVED from validation data only — they are never hardcoded per
 * market or league. The thresholds live in config('evaluation.status_classification')
 * so admins can tune them without code changes.
 *
 *   INSUFFICIENT_DATA  resolved < minimum_sample (or no accuracy)
 *   WEAK               accuracy < weak_accuracy
 *   NEUTRAL            weak_accuracy <= accuracy < promising_accuracy
 *   PROMISING          promising_accuracy <= accuracy < strong_accuracy
 *   STRONG             accuracy >= strong_accuracy AND brier <= strong_brier_max
 */
class StatusClassificationService
{
    public const STRONG = 'STRONG';
    public const PROMISING = 'PROMISING';
    public const NEUTRAL = 'NEUTRAL';
    public const WEAK = 'WEAK';
    public const INSUFFICIENT_DATA = 'INSUFFICIENT_DATA';

    public const LABELS = [
        self::STRONG,
        self::PROMISING,
        self::NEUTRAL,
        self::WEAK,
        self::INSUFFICIENT_DATA,
    ];

    /**
     * @param array<string,mixed> $metrics A summary array with keys
     *        `resolved`, `accuracy` (0-100|null) and `brier_score` (0-1|null).
     */
    public function classify(array $metrics): string
    {
        $resolved = (int) ($metrics['resolved'] ?? 0);
        $accuracy = $metrics['accuracy'] ?? null;
        $brier = $metrics['brier_score'] ?? null;

        $cfg = config('evaluation.status_classification', []);

        if ($resolved < (int) ($cfg['minimum_sample'] ?? 100) || $accuracy === null) {
            return self::INSUFFICIENT_DATA;
        }

        $accuracy = (float) $accuracy;
        $strong = (float) ($cfg['strong_accuracy'] ?? 62.0);
        $promising = (float) ($cfg['promising_accuracy'] ?? 55.0);
        $weak = (float) ($cfg['weak_accuracy'] ?? 50.0);
        $strongBrierMax = (float) ($cfg['strong_brier_max'] ?? 0.23);

        if ($accuracy < $weak) {
            return self::WEAK;
        }

        if ($accuracy >= $strong && ($brier === null || (float) $brier <= $strongBrierMax)) {
            return self::STRONG;
        }

        if ($accuracy >= $promising) {
            return self::PROMISING;
        }

        return self::NEUTRAL;
    }

    /**
     * Add a derived `status` key to a metrics summary in place.
     *
     * @param array<string,mixed> $metrics
     * @return array<string,mixed>
     */
    public function withStatus(array $metrics): array
    {
        $metrics['status'] = $this->classify($metrics);

        return $metrics;
    }
}
