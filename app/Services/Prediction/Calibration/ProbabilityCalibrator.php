<?php

namespace App\Services\Prediction\Calibration;

/**
 * Calibrates raw model probabilities against observed outcomes.
 *
 * Supported methods:
 *   - Platt scaling:   sigmoid(a * logit(p) + b), fit via gradient descent.
 *   - Isotonic regression: monotone piecewise mapping via PAV.
 *
 * Probabilities are expressed on the 0–100 scale used throughout the app.
 * The calibrator is a pure, deterministic object: it only learns from the
 * (probability, outcome) pairs it is given, so it never touches future data.
 */
class ProbabilityCalibrator
{
    public const PLATT = 'platt';
    public const ISOTONIC = 'isotonic';

    protected const EPS = 1e-7;

    protected string $method = self::PLATT;

    /** @var float|null Platt slope */
    protected ?float $a = null;

    /** @var float|null Platt intercept */
    protected ?float $b = null;

    /** @var list<array{x:float,y:float}> isotonic breakpoints (prob, success-rate) in 0-100 */
    protected array $isotonic = [];

    /**
     * @param list<float> $probabilities raw probabilities (0-100)
     * @param list<int>   $outcomes      1 = won, 0 = lost (same length)
     * @param int         $maxIterations  gradient-descent cap (Platt only)
     * @param float       $learningRate   gradient-descent step (Platt only)
     */
    public function fit(array $probabilities, array $outcomes, string $method = self::PLATT, int $maxIterations = 3000, float $learningRate = 0.5): self
    {
        if (count($probabilities) !== count($outcomes) || count($probabilities) === 0) {
            throw new \InvalidArgumentException('Calibration requires equal-length, non-empty inputs.');
        }

        $this->method = $method;

        if ($method === self::ISOTONIC) {
            $this->fitIsotonic($probabilities, $outcomes);
        } else {
            $this->fitPlatt($probabilities, $outcomes, $maxIterations, $learningRate);
        }

        return $this;
    }

    /**
     * Return the calibrated probability (0-100) for a raw probability (0-100).
     */
    public function predict(float $probability): float
    {
        $p = $this->clamp01($probability / 100.0);

        if ($this->method === self::ISOTONIC) {
            $calibrated = $this->predictIsotonic($probability);
        } else {
            $calibrated = $this->sigmoid($this->a * $this->logit($p) + $this->b);
        }

        return round($this->clamp01($calibrated) * 100.0, 2);
    }

    /**
     * Serializable parameters for storing in a model configuration.
     *
     * @return array<string,mixed>
     */
    public function parameters(): array
    {
        return [
            'method' => $this->method,
            'a' => $this->a,
            'b' => $this->b,
            'isotonic' => $this->isotonic,
        ];
    }

    /**
     * Reconstruct a fitted calibrator from stored parameters.
     *
     * @param array<string,mixed> $parameters
     */
    public static function fromParameters(array $parameters): self
    {
        $c = new self();

        $c->method = $parameters['method'] ?? self::PLATT;

        if ($c->method === self::ISOTONIC) {
            $c->isotonic = $parameters['isotonic'] ?? [];
        } else {
            $c->a = isset($parameters['a']) ? (float) $parameters['a'] : null;
            $c->b = isset($parameters['b']) ? (float) $parameters['b'] : null;
        }

        return $c;
    }

    /**
     * @param list<float> $probabilities
     * @param list<int>   $outcomes
     */
    protected function fitPlatt(array $probabilities, array $outcomes, int $maxIterations = 3000, float $learningRate = 0.5): void
    {
        $xs = [];
        $ys = [];

        foreach ($probabilities as $i => $raw) {
            $xs[] = $this->logit($this->clamp01($raw / 100.0));
            $ys[] = (int) $outcomes[$i] === 1 ? 1.0 : 0.0;
        }

        // Full-batch gradient descent on the two Platt parameters.
        $a = 0.0;
        $b = 0.0;
        $lr = $learningRate;
        $n = count($xs);

        for ($it = 0; $it < $maxIterations; $it++) {
            $ga = 0.0;
            $gb = 0.0;

            for ($i = 0; $i < $n; $i++) {
                $z = $a * $xs[$i] + $b;
                $sig = $this->sigmoid($z);
                $err = $sig - $ys[$i];
                $ga += $err * $xs[$i];
                $gb += $err;
            }

            $ga /= $n;
            $gb /= $n;

            $a -= $lr * $ga;
            $b -= $lr * $gb;

            if (abs($ga) < 1e-6 && abs($gb) < 1e-6) {
                break;
            }
        }

        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Pool-adjacent-violators isotonic regression.
     *
     * @param list<float> $probabilities
     * @param list<int>   $outcomes
     */
    protected function fitIsotonic(array $probabilities, array $outcomes): void
    {
        $pairs = [];

        foreach ($probabilities as $i => $raw) {
            $pairs[] = ['x' => (float) $raw, 'y' => ((int) $outcomes[$i] === 1 ? 1.0 : 0.0)];
        }

        usort($pairs, fn ($a, $b) => $a['x'] <=> $b['x']);

        // PAV blocks: each block holds x-sum, y-sum, count.
        $blocks = [];

        foreach ($pairs as $pair) {
            $blocks[] = ['sx' => $pair['x'], 'sy' => $pair['y'], 'n' => 1];

            while (count($blocks) >= 2) {
                $last = $blocks[count($blocks) - 1];
                $prev = $blocks[count($blocks) - 2];

                $lastMean = $last['sy'] / $last['n'];
                $prevMean = $prev['sy'] / $prev['n'];

                if ($prevMean <= $lastMean) {
                    break;
                }

                // Violation: merge the two blocks.
                array_pop($blocks);
                $blocks[count($blocks) - 1] = [
                    'sx' => $prev['sx'] + $last['sx'],
                    'sy' => $prev['sy'] + $last['sy'],
                    'n' => $prev['n'] + $last['n'],
                ];
            }
        }

        $this->isotonic = [];

        foreach ($blocks as $block) {
            $this->isotonic[] = [
                'x' => round($block['sx'] / $block['n'], 4),
                'y' => round($block['sy'] / $block['n'] * 100.0, 4),
            ];
        }
    }

    protected function predictIsotonic(float $probability): float
    {
        if (empty($this->isotonic)) {
            return $probability / 100.0;
        }

        $breakpoints = $this->isotonic;

        // Below first breakpoint: use its value.
        if ($probability <= $breakpoints[0]['x']) {
            return $breakpoints[0]['y'] / 100.0;
        }

        // Above last breakpoint: use its value.
        $last = $breakpoints[count($breakpoints) - 1];

        if ($probability >= $last['x']) {
            return $last['y'] / 100.0;
        }

        // Linear interpolation between surrounding breakpoints.
        for ($i = 0; $i < count($breakpoints) - 1; $i++) {
            $lo = $breakpoints[$i];
            $hi = $breakpoints[$i + 1];

            if ($probability >= $lo['x'] && $probability <= $hi['x']) {
                if ($hi['x'] === $lo['x']) {
                    return $hi['y'] / 100.0;
                }

                $t = ($probability - $lo['x']) / ($hi['x'] - $lo['x']);

                return ($lo['y'] + $t * ($hi['y'] - $lo['y'])) / 100.0;
            }
        }

        return $probability / 100.0;
    }

    protected function logit(float $p): float
    {
        $p = $this->clamp01($p);
        $p = max(self::EPS, min(1.0 - self::EPS, $p));

        return log($p / (1.0 - $p));
    }

    protected function sigmoid(float $z): float
    {
        // Numerically stable sigmoid.
        if ($z >= 0) {
            $exp = exp(-$z);

            return 1.0 / (1.0 + $exp);
        }

        $exp = exp($z);

        return $exp / (1.0 + $exp);
    }

    protected function clamp01(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }
}
