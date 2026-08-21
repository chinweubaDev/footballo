<?php

namespace App\Services\Prediction\Support;

class EnsembleResult
{
    /**
     * @param array<string,float> $scoreMatrix map of "i-j" => probability (%)
     * @param array<string,float> $doubleChance ['1x' => %, 'x2' => %, '12' => %]
     * @param list<array{score:string,probability:float}> $correctScores
     * @param list<ModelPrediction> $modelPredictions
     */
    public function __construct(
        public float $homeProbability,
        public float $drawProbability,
        public float $awayProbability,
        public float $expectedHomeGoals,
        public float $expectedAwayGoals,
        public array $scoreMatrix,
        public float $over15Probability,
        public float $over25Probability,
        public float $bttsProbability,
        public array $doubleChance,
        public array $correctScores,
        public float $modelAgreement,
        public array $modelPredictions,
        public int $dataQuality,
        public array $features,
    ) {
    }
}
