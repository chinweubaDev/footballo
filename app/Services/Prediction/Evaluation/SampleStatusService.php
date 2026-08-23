<?php

namespace App\Services\Prediction\Evaluation;

/**
 * Phase 1K — live evidence strength labels.
 *
 *   INSUFFICIENT        resolved < preliminary
 *   PRELIMINARY         preliminary <= resolved < meaningful
 *   MEANINGFUL          meaningful <= resolved < strong
 *   STRONGER EVIDENCE   resolved >= strong
 */
class SampleStatusService
{
    public const INSUFFICIENT = 'INSUFFICIENT';
    public const PRELIMINARY = 'PRELIMINARY';
    public const MEANINGFUL = 'MEANINGFUL';
    public const STRONGER_EVIDENCE = 'STRONGER EVIDENCE';

    public function label(int $resolved): string
    {
        $cfg = config('evaluation.evidence', []);
        $preliminary = (int) ($cfg['preliminary'] ?? 50);
        $meaningful = (int) ($cfg['meaningful'] ?? 100);
        $strong = (int) ($cfg['strong'] ?? 500);

        if ($resolved < $preliminary) {
            return self::INSUFFICIENT;
        }

        if ($resolved < $meaningful) {
            return self::PRELIMINARY;
        }

        if ($resolved < $strong) {
            return self::MEANINGFUL;
        }

        return self::STRONGER_EVIDENCE;
    }
}
