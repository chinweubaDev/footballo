<?php

namespace App\Services\Prediction\Validation;

use App\Services\Prediction\Calibration\ModelComparisonService;

/**
 * Statistical model comparison (Phase 1G §13).
 *
 * Compares two model versions using a two-proportion z-test on accuracy.
 * The system never claims improvement unless the difference is statistically
 * significant AND both samples are sufficient — otherwise it reports
 * "Insufficient evidence of improvement."
 */
class SignificanceService
{
    public const VERDICT_IMPROVEMENT = 'improvement';
    public const VERDICT_REGRESSION = 'regression';
    public const VERDICT_EQUIVALENT = 'equivalent';
    public const VERDICT_INSUFFICIENT = 'insufficient';

    protected const MARKETS = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];

    public function __construct(protected ModelComparisonService $comparison)
    {
    }

    /**
     * Compare two model versions overall and per market.
     *
     * @return array<string,mixed>
     */
    public function compareVersions(string $versionA, string $versionB): array
    {
        $dataA = $this->comparison->summarizeVersion($versionA);
        $dataB = $this->comparison->summarizeVersion($versionB);

        $overviewA = $dataA['overview'] ?? [];
        $overviewB = $dataB['overview'] ?? [];

        $markets = [];

        foreach (self::MARKETS as $market) {
            $a = $dataA['by_market'][$market] ?? null;
            $b = $dataB['by_market'][$market] ?? null;

            $markets[$market] = $this->compareProportions(
                (int) ($a['won'] ?? 0),
                (int) ($a['resolved'] ?? 0),
                (int) ($b['won'] ?? 0),
                (int) ($b['resolved'] ?? 0),
            );
            $markets[$market]['market_code'] = $market;
        }

        return [
            'version_a' => $versionA,
            'version_b' => $versionB,
            'overall' => $this->compareProportions(
                (int) ($overviewA['won'] ?? 0),
                (int) ($overviewA['resolved'] ?? 0),
                (int) ($overviewB['won'] ?? 0),
                (int) ($overviewB['resolved'] ?? 0),
            ),
            'markets' => $markets,
            'alpha' => (float) config('evaluation.significance.alpha', 0.05),
            'minimum_sample' => (int) config('evaluation.significance.minimum_sample', 100),
        ];
    }

    /**
     * Two-proportion z-test comparing accuracy of A vs B.
     *
     * A positive diff means B is better than A (accuracy points).
     *
     * @return array<string,mixed>
     */
    public function compareProportions(int $wonA, int $nA, int $wonB, int $nB): array
    {
        $minSample = (int) config('evaluation.significance.minimum_sample', 100);

        $base = [
            'a_won' => $wonA,
            'a_n' => $nA,
            'a_accuracy' => $nA > 0 ? round($wonA / $nA * 100, 2) : null,
            'b_won' => $wonB,
            'b_n' => $nB,
            'b_accuracy' => $nB > 0 ? round($wonB / $nB * 100, 2) : null,
            'diff_points' => null,
            'p_value' => null,
            'ci_lower' => null,
            'ci_upper' => null,
            'verdict' => self::VERDICT_INSUFFICIENT,
            'message' => 'Insufficient evidence of improvement.',
        ];

        if ($nA < $minSample || $nB < $minSample) {
            $base['message'] = "Insufficient evidence of improvement (sample sizes {$nA} and {$nB} below minimum {$minSample}).";

            return $base;
        }

        $pa = $wonA / $nA;
        $pb = $wonB / $nB;
        $diff = ($pb - $pa) * 100; // accuracy points

        $pooled = ($wonA + $wonB) / ($nA + $nB);

        // Unpooled standard error for the difference (standard two-proportion CI).
        $seDiff = sqrt(
            ($pa * (1 - $pa) / $nA) + ($pb * (1 - $pb) / $nB)
        );

        // Pooled standard error for the hypothesis test (H0: pa == pb).
        $sePooled = sqrt($pooled * (1 - $pooled) * (1 / $nA + 1 / $nB));

        $z = $sePooled > 0 ? ($pb - $pa) / $sePooled : 0.0;
        $pValue = $this->twoSidedPValue($z);

        $zCritical = $this->zCritical();
        $ciLower = ($diff - $zCritical * $seDiff * 100);
        $ciUpper = ($diff + $zCritical * $seDiff * 100);

        $base['diff_points'] = round($diff, 2);
        $base['p_value'] = round($pValue, 4);
        $base['ci_lower'] = round($ciLower, 2);
        $base['ci_upper'] = round($ciUpper, 2);

        $alpha = (float) config('evaluation.significance.alpha', 0.05);

        if ($pValue >= $alpha) {
            $base['verdict'] = self::VERDICT_INSUFFICIENT;
            $base['message'] = 'Insufficient evidence of improvement.';
        } elseif ($diff > 0) {
            $base['verdict'] = self::VERDICT_IMPROVEMENT;
            $base['message'] = sprintf('Statistically significant improvement (+%.2f pts, p=%.4f).', $diff, $pValue);
        } elseif ($diff < 0) {
            $base['verdict'] = self::VERDICT_REGRESSION;
            $base['message'] = sprintf('Statistically significant regression (%.2f pts, p=%.4f).', $diff, $pValue);
        } else {
            $base['verdict'] = self::VERDICT_EQUIVALENT;
            $base['message'] = 'Equivalent performance.';
        }

        return $base;
    }

    /**
     * z-score for the configured two-sided confidence level.
     */
    public function zCritical(): float
    {
        $alpha = (float) config('evaluation.significance.alpha', 0.05);

        return $this->inverseNormalCdf(1 - $alpha / 2);
    }

    /**
     * Two-sided p-value from a z statistic.
     */
    protected function twoSidedPValue(float $z): float
    {
        return 2 * (1 - $this->normalCdf(abs($z)));
    }

    /**
     * Standard normal CDF via the erf approximation (Abramowitz & Stegun 7.1.26).
     */
    public function normalCdf(float $x): float
    {
        return 0.5 * (1 + $this->erf($x / sqrt(2)));
    }

    /**
     * Inverse standard normal CDF (Acklam's algorithm) — accuracy ~1e-9.
     */
    public function inverseNormalCdf(float $p): float
    {
        if ($p <= 0.0) {
            return -INF;
        }

        if ($p >= 1.0) {
            return INF;
        }

        $a = [
            -3.969683028665376e+01, 2.209460984245205e+02, -2.759285104469687e+02,
            1.383577518672690e+02, -3.066479806614716e+01, 2.506628277459239e+00,
        ];
        $b = [
            -5.447609879822406e+01, 1.615858368580409e+02, -1.556989798598866e+02,
            6.680131188771972e+01, -1.328068155288572e+01,
        ];
        $c = [
            -7.784894002430293e-03, -3.223964580411365e-01, -2.400758277161838e+00,
            -2.549732539343734e+00, 4.374664141464968e+00, 2.938163982698783e+00,
        ];
        $d = [
            7.784695709041462e-03, 3.224671290700398e-01, 2.445134137142996e+00,
            3.754408661907416e+00,
        ];

        $plow = 0.02425;
        $phigh = 1 - $plow;

        if ($p < $plow) {
            $q = sqrt(-2 * log($p));

            return ((((($c[0] * $q + $c[1]) * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5])
                / (((($d[0] * $q + $d[1]) * $q + $d[2]) * $q + $d[3]) * $q + 1);
        }

        if ($p > $phigh) {
            $q = sqrt(-2 * log(1 - $p));

            return -((((($c[0] * $q + $c[1]) * $q + $c[2]) * $q + $c[3]) * $q + $c[4]) * $q + $c[5])
                / (((($d[0] * $q + $d[1]) * $q + $d[2]) * $q + $d[3]) * $q + 1);
        }

        $q = $p - 0.5;
        $r = $q * $q;

        return (((((($a[0] * $r + $a[1]) * $r + $a[2]) * $r + $a[3]) * $r + $a[4]) * $r + $a[5]) * $q)
            / ((((($b[0] * $r + $b[1]) * $r + $b[2]) * $r + $b[3]) * $r + $b[4]) * $r + 1);
    }

    /**
     * Error function approximation (Abramowitz & Stegun 7.1.26), |error| < 1.5e-7.
     */
    public function erf(float $x): float
    {
        $sign = $x < 0 ? -1.0 : 1.0;
        $x = abs($x);

        $t = 1 / (1 + 0.3275911 * $x);
        $y = 1 - (((((1.061405429 * $t - 1.453152027) * $t) + 1.421413741) * $t - 0.284496736) * $t + 0.254829592) * $t * exp(-$x * $x);

        return $sign * $y;
    }
}
