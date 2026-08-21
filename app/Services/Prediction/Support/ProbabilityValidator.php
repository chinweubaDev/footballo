<?php

namespace App\Services\Prediction\Support;

class ProbabilityValidator
{
    public const TOLERANCE = 0.01;

    public static function clamp(float $value): float
    {
        return max(0.0, min(100.0, $value));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function assertProbability(float $value, string $label = 'probability'): void
    {
        if ($value < -self::TOLERANCE || $value > 100.0 + self::TOLERANCE) {
            throw new \InvalidArgumentException("{$label} out of range [0,100]: {$value}");
        }
    }

    /**
     * Ensure a set of mutually exclusive probabilities sums to ~100.
     *
     * @throws \RuntimeException
     */
    public static function assertExhaustive(array $probabilities, string $label = 'probabilities'): void
    {
        $sum = array_sum($probabilities);

        if (abs($sum - 100.0) > 0.5) {
            throw new \RuntimeException("{$label} must sum to 100, got {$sum}");
        }
    }

    /**
     * Normalise an array of probabilities to sum to 100.
     */
    public static function normalize(array $probabilities): array
    {
        $sum = array_sum($probabilities);

        if ($sum <= 0.0) {
            return $probabilities;
        }

        return array_map(fn ($p) => round(($p / $sum) * 100.0, 2), $probabilities);
    }
}
