<?php

namespace App\Services\Prediction\Support;

/**
 * Deterministic Poisson score matrix helper. All market probability
 * calculations that depend on the goal distribution derive from this class so
 * the math is implemented once and never duplicated.
 */
class PoissonMatrix
{
    /**
     * Poisson probability mass function: P(X = k) = e^-λ * λ^k / k!
     */
    public static function pmf(int $k, float $lambda): float
    {
        if ($lambda < 0.0) {
            $lambda = 0.0;
        }

        return exp(-$lambda) * pow($lambda, $k) / self::factorial($k);
    }

    public static function factorial(int $n): int
    {
        $result = 1;

        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }

        return $result;
    }

    /**
     * Build a normalised score matrix for home/away goals up to $maxGoals.
     *
     * The truncated matrix is normalised so probabilities sum to ~100% within
     * the configured range (goals above the range are negligible for typical λ).
     *
     * @return array<string,float> map of "i-j" => probability (%)
     */
    public static function build(float $lambdaHome, float $lambdaAway, int $maxGoals = 6): array
    {
        $home = [];
        $away = [];

        for ($i = 0; $i <= $maxGoals; $i++) {
            $home[$i] = self::pmf($i, $lambdaHome);
            $away[$i] = self::pmf($i, $lambdaAway);
        }

        $matrix = [];
        $sum = 0.0;

        for ($i = 0; $i <= $maxGoals; $i++) {
            for ($j = 0; $j <= $maxGoals; $j++) {
                $p = $home[$i] * $away[$j];
                $matrix["{$i}-{$j}"] = $p;
                $sum += $p;
            }
        }

        if ($sum <= 0.0) {
            return $matrix;
        }

        foreach ($matrix as $score => $p) {
            $matrix[$score] = round(($p / $sum) * 100.0, 2);
        }

        return $matrix;
    }

    /**
     * 1X2 probabilities derived from the score matrix.
     *
     * @return array{home:float,draw:float,away:float}
     */
    public static function oneXTwo(array $matrix): array
    {
        $home = 0.0;
        $draw = 0.0;
        $away = 0.0;

        foreach ($matrix as $score => $p) {
            [$i, $j] = array_map('intval', explode('-', $score));

            if ($i > $j) {
                $home += $p;
            } elseif ($i === $j) {
                $draw += $p;
            } else {
                $away += $p;
            }
        }

        return [
            'home' => round($home, 2),
            'draw' => round($draw, 2),
            'away' => round($away, 2),
        ];
    }

    /**
     * Probability of total goals exceeding the given threshold (e.g. 1.5, 2.5).
     */
    public static function over(array $matrix, float $threshold): float
    {
        $minGoals = (int) floor($threshold) + 1;
        $sum = 0.0;

        foreach ($matrix as $score => $p) {
            [$i, $j] = array_map('intval', explode('-', $score));

            if ($i + $j >= $minGoals) {
                $sum += $p;
            }
        }

        return round($sum, 2);
    }

    /**
     * Probability both teams score at least one goal.
     */
    public static function btts(array $matrix): float
    {
        $sum = 0.0;

        foreach ($matrix as $score => $p) {
            [$i, $j] = array_map('intval', explode('-', $score));

            if ($i > 0 && $j > 0) {
                $sum += $p;
            }
        }

        return round($sum, 2);
    }

    /**
     * Top N most likely scorelines.
     *
     * @return list<array{score:string,probability:float}>
     */
    public static function topScores(array $matrix, int $limit = 5): array
    {
        arsort($matrix);
        $top = array_slice($matrix, 0, $limit, true);

        $result = [];

        foreach ($top as $score => $probability) {
            $result[] = ['score' => $score, 'probability' => $probability];
        }

        return $result;
    }
}
