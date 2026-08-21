<?php

namespace App\Services\Prediction\Confidence;

use App\Services\Prediction\Support\EnsembleResult;
use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

/**
 * Produces a 0-100 confidence score that is deliberately INDEPENDENT of the
 * market probability. It weighs probability strength, model agreement, data
 * quality, market agreement and form consistency.
 */
class ConfidenceEngine
{
    /**
     * @return array{score:int,level:string,factors:array<string,float>}
     */
    public function calculate(PredictionContext $context, string $marketCode, string $selection, float $probability, EnsembleResult $result): array
    {
        $factors = [
            'probability_strength' => $this->probabilityStrength($probability),
            'model_agreement' => $this->modelAgreementFactor($marketCode, $result),
            'data_quality' => (float) $result->dataQuality,
            'market_agreement' => $this->marketAgreement($context, $marketCode, $selection, $probability),
            'form_consistency' => $this->formConsistency($result),
        ];

        $weights = config('prediction.confidence.weights', [
            'probability_strength' => 30,
            'model_agreement' => 25,
            'data_quality' => 20,
            'market_agreement' => 15,
            'form_consistency' => 10,
        ]);

        $score = 0.0;

        foreach ($factors as $key => $value) {
            $score += $value * ($weights[$key] ?? 0) / 100.0;
        }

        $score = (int) round(ProbabilityValidator::clamp($score));

        return [
            'score' => $score,
            'level' => $this->level($score),
            'factors' => $factors,
        ];
    }

    protected function probabilityStrength(float $probability): float
    {
        return ProbabilityValidator::clamp(($probability - 50.0) * 2.0);
    }

    protected function modelAgreementFactor(string $marketCode, EnsembleResult $result): float
    {
        // Goal-based markets derive from a single Poisson distribution, so
        // model agreement is only meaningful for outcome-based markets. For
        // goal markets, data quality is used as the agreement proxy.
        if (in_array($marketCode, ['1x2', 'draw', 'double_chance'], true)) {
            return $result->modelAgreement;
        }

        return (float) $result->dataQuality;
    }

    protected function marketAgreement(PredictionContext $context, string $marketCode, string $selection, float $probability): float
    {
        $odds = $context->odds;
        $oddsAvailable = (bool) ($odds['available'] ?? false);

        return match ($marketCode) {
            '1x2' => $this->oneXTwoMarketAgreement($context, $selection),
            'draw' => $oddsAvailable && ($odds['draw_imp'] ?? 0) > 0
                ? $this->distanceAgreement((float) $odds['draw_imp'], $probability)
                : 50.0,
            'double_chance' => $this->doubleChanceAgreement($context, $selection, $probability),
            'over_1_5' => $oddsAvailable && ($odds['o15_imp'] ?? 0) > 0
                ? $this->distanceAgreement((float) $odds['o15_imp'], $probability)
                : 50.0,
            'over_2_5' => $oddsAvailable && ($odds['o25_imp'] ?? 0) > 0
                ? $this->distanceAgreement((float) $odds['o25_imp'], $probability)
                : 50.0,
            'btts' => $this->bttsAgreement($odds, $selection, $probability),
            default => 50.0,
        };
    }

    protected function oneXTwoMarketAgreement(PredictionContext $context, string $selection): float
    {
        $odds = $context->odds;
        $api = $context->apiPrediction;
        $oddsAvailable = (bool) ($odds['available'] ?? false);
        $apiAvailable = (bool) ($api['available'] ?? false);

        $oddsPick = null;
        $apiPick = null;

        if ($oddsAvailable) {
            $picks = ['home' => $odds['home_imp'] ?? 0, 'draw' => $odds['draw_imp'] ?? 0, 'away' => $odds['away_imp'] ?? 0];
            $oddsPick = array_search(max($picks), $picks, true);
        }

        if ($apiAvailable) {
            $picks = ['home' => $api['hp'] ?? 0, 'draw' => $api['dp'] ?? 0, 'away' => $api['ap'] ?? 0];
            $apiPick = array_search(max($picks), $picks, true);
        }

        if ($oddsAvailable && $apiAvailable) {
            $score = 0.0;
            $score += $oddsPick === $selection ? 70.0 : 0.0;
            $score += $apiPick === $selection ? 30.0 : 0.0;

            return $score;
        }

        if ($oddsAvailable) {
            return $oddsPick === $selection ? 100.0 : 0.0;
        }

        if ($apiAvailable) {
            return $apiPick === $selection ? 100.0 : 0.0;
        }

        return 50.0;
    }

    protected function doubleChanceAgreement(PredictionContext $context, string $selection, float $probability): float
    {
        $odds = $context->odds;

        if (! ($odds['available'] ?? false)) {
            return 50.0;
        }

        $home = (float) ($odds['home_imp'] ?? 0);
        $draw = (float) ($odds['draw_imp'] ?? 0);
        $away = (float) ($odds['away_imp'] ?? 0);

        $implied = match ($selection) {
            '1x' => $home + $draw,
            'x2' => $draw + $away,
            default => $home + $away,
        };

        return $implied > 0 ? $this->distanceAgreement($implied, $probability) : 50.0;
    }

    protected function bttsAgreement(array $odds, string $selection, float $probability): float
    {
        if (! ($odds['available'] ?? false) || ($odds['bts_imp'] ?? 0) <= 0) {
            return 50.0;
        }

        $implied = $selection === 'yes' ? (float) $odds['bts_imp'] : 100.0 - (float) $odds['bts_imp'];

        return $this->distanceAgreement($implied, $probability);
    }

    protected function distanceAgreement(float $implied, float $probability): float
    {
        return ProbabilityValidator::clamp(100.0 - abs($implied - $probability) * 3.0);
    }

    protected function formConsistency(EnsembleResult $result): float
    {
        $diff = ($result->features['home_form_score'] ?? 50) - ($result->features['away_form_score'] ?? 50);

        return ProbabilityValidator::clamp(50.0 + $diff * 0.5);
    }

    protected function level(int $score): string
    {
        $levels = config('prediction.confidence.levels', ['low' => 50, 'moderate' => 65, 'high' => 80]);

        if ($score >= $levels['high']) {
            return 'very_high';
        }

        if ($score >= $levels['moderate']) {
            return 'high';
        }

        if ($score >= $levels['low']) {
            return 'moderate';
        }

        return 'low';
    }
}
