<?php

namespace App\Services\Prediction\Support;

class ModelPrediction
{
    public function __construct(
        public string $name,
        public float $homeProbability,
        public float $drawProbability,
        public float $awayProbability,
        public bool $available = true,
        public ?float $expectedHomeGoals = null,
        public ?float $expectedAwayGoals = null,
    ) {
    }

    public function topPick(): string
    {
        $probs = [
            'home' => $this->homeProbability,
            'draw' => $this->drawProbability,
            'away' => $this->awayProbability,
        ];

        arsort($probs);

        return (string) array_key_first($probs);
    }
}
