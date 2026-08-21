<?php

namespace App\Services\Prediction\Evaluation;

/**
 * Structured market result resolution.
 *
 * Determines whether a predicted selection WON or LOST against an actual
 * scoreline. This deliberately does NOT use text matching: it works off the
 * structured market_code + selection produced by the Phase 1B markets.
 *
 * Returns one of: 'won', 'lost', 'void'.
 * 'void' is returned when the market/selection cannot be resolved (e.g. an
 * unknown market code) — the caller decides how to handle it.
 */
class MarketResultResolver
{
    public const WON = 'won';
    public const LOST = 'lost';
    public const VOID = 'void';

    /**
     * Resolve a single selection against a final score.
     */
    public function resolve(string $marketCode, string $selection, int $homeGoals, int $awayGoals): string
    {
        $total = $homeGoals + $awayGoals;

        return match ($marketCode) {
            '1x2' => $this->oneXTwo($selection, $homeGoals, $awayGoals),
            'draw' => $this->draw($selection, $homeGoals, $awayGoals),
            'double_chance' => $this->doubleChance($selection, $homeGoals, $awayGoals),
            'over_1_5' => $this->goalsLine($selection, $total, 1.5),
            'over_2_5' => $this->goalsLine($selection, $total, 2.5),
            'btts' => $this->btts($selection, $homeGoals, $awayGoals),
            'correct_score' => $this->correctScore($selection, $homeGoals, $awayGoals),
            default => self::VOID,
        };
    }

    protected function oneXTwo(string $selection, int $home, int $away): string
    {
        return match ($selection) {
            'home' => $this->outcome($home > $away),
            'draw' => $this->outcome($home === $away),
            'away' => $this->outcome($home < $away),
            default => self::VOID,
        };
    }

    protected function draw(string $selection, int $home, int $away): string
    {
        if ($selection !== 'draw') {
            return self::VOID;
        }

        return $this->outcome($home === $away);
    }

    protected function doubleChance(string $selection, int $home, int $away): string
    {
        return match ($selection) {
            '1x' => $this->outcome($home >= $away),          // Home win or draw
            'x2' => $this->outcome($home <= $away),          // Draw or away win
            '12' => $this->outcome($home !== $away),         // Home win or away win
            default => self::VOID,
        };
    }

    /**
     * @param float $line the Asian-style line, e.g. 1.5 or 2.5
     */
    protected function goalsLine(string $selection, int $total, float $line): string
    {
        $suffix = str_replace('.', '_', (string) $line);
        $over = 'over_'.$suffix;
        $under = 'under_'.$suffix;

        if ($selection === $over) {
            return $this->outcome($total > $line);
        }

        if ($selection === $under) {
            return $this->outcome($total <= $line);
        }

        return self::VOID;
    }

    protected function btts(string $selection, int $home, int $away): string
    {
        $bothScored = $home >= 1 && $away >= 1;

        return match ($selection) {
            'yes' => $this->outcome($bothScored),
            'no' => $this->outcome(! $bothScored),
            default => self::VOID,
        };
    }

    protected function correctScore(string $selection, int $home, int $away): string
    {
        // Selection is a scoreline like "2-1".
        if (! preg_match('/^\d+-\d+$/', $selection)) {
            return self::VOID;
        }

        return $this->outcome($selection === "{$home}-{$away}");
    }

    protected function outcome(bool $condition): string
    {
        return $condition ? self::WON : self::LOST;
    }
}
