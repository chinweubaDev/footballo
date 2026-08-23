<?php

namespace App\Services\Prediction;

use App\Models\Prediction;
use Illuminate\Support\Collection;

/**
 * Phase 1Q — premium accumulator builder.
 *
 * Builds accumulator tickets that reach a TARGET total odds using at most
 * MAX_LEGS matches, drawing from a mix of markets. The strongest
 * (highest-confidence) selections are added first; the ticket stops once the
 * target odds are reached.
 *
 *   VIP  -> 3.0-odds + 5.0-odds tickets
 *   VVIP -> 2.0-odds + 5.0-odds + 10.0-odds tickets
 *
 * Every ticket reports its REAL combined probability and total odds. Odds are
 * bookmaker odds where available, otherwise model-implied fair odds (labelled).
 * Nothing here promises or guarantees a win.
 */
class AccumulatorBuilderService
{
    /** @var list<float> total-odds targets per tier. */
    public const VIP_TARGETS = [3.0, 5.0];

    /** @var list<float> */
    public const VVIP_TARGETS = [2.0, 5.0, 10.0];

    /** Maximum matches per accumulator. */
    public const MAX_LEGS = 5;

    /** Minimum odds per individual leg (bookmaker or model-implied). */
    public const MIN_ODDS = 1.20;

    /**
     * Markets eligible for accumulators. Correct Score and Draw are excluded
     * (structurally very low accuracy). 1X2 is included so high-odds targets
     * (e.g. 10.0) are reachable within MAX_LEGS.
     */
    protected const MARKETS = ['1x2', 'double_chance', 'over_1_5', 'over_2_5', 'btts'];

    /**
     * Round-robin order used to diversify the markets in each ticket. Over 1.5
     * is intentionally NOT first, so tickets are a genuine mix rather than a
     * stack of Over 1.5 legs.
     */
    protected const MARKET_ORDER = ['double_chance', '1x2', 'over_2_5', 'btts', 'over_1_5'];

    /**
     * @return list<array<string,mixed>>
     */
    public function build(string $tier): array
    {
        $targets = $tier === 'vvip' ? self::VVIP_TARGETS : self::VIP_TARGETS;
        $selections = $this->selections();

        $tickets = [];

        foreach ($targets as $index => $target) {
            $tickets[] = $this->ticket($selections, (float) $target, $index + 1);
        }

        return $tickets;
    }

    /**
     * Strongest published selections for upcoming fixtures, best first.
     */
    protected function selections(): Collection
    {
        return Prediction::query()
            ->where('status', 'published')
            ->whereIn('market_code', self::MARKETS)
            ->whereHas('league', fn ($q) => $q->where('enabled', true)->where('prediction_enabled', true))
            ->whereHas('fixture', fn ($q) => $q
                ->whereIn('status', ['NS', 'TBD', 'PST'])
                ->where('match_date', '>=', now()->startOfDay()))
            ->with(['fixture', 'league'])
            ->orderByDesc('confidence')
            ->orderByDesc('probability')
            ->get();
    }

    /**
     * Build one ticket by round-robin across markets: take the strongest pick
     * of each market in turn (never repeating a match) until the target odds
     * are reached or MAX_LEGS is hit. This keeps a genuine market mix.
     *
     * @return array<string,mixed>
     */
    protected function ticket(Collection $selections, float $targetOdds, int $number): array
    {
        // Group by market, confidence-descending within each group.
        $byMarket = [];

        foreach ($selections as $prediction) {
            $byMarket[$prediction->market_code][] = $prediction;
        }

        $ordered = array_merge(
            array_intersect(self::MARKET_ORDER, array_keys($byMarket)),
            array_diff(array_keys($byMarket), self::MARKET_ORDER),
        );

        $pointers = array_fill_keys($ordered, 0);

        $legs = [];
        $usedFixtures = [];
        $totalOdds = 1.0;

        while (count($legs) < self::MAX_LEGS) {
            $progressed = false;

            foreach ($ordered as $market) {
                if (count($legs) >= self::MAX_LEGS || $totalOdds >= $targetOdds) {
                    break;
                }

                while (isset($byMarket[$market][$pointers[$market]])) {
                    $candidate = $byMarket[$market][$pointers[$market]];
                    $pointers[$market]++;

                    // Never put the same match twice in one accumulator.
                    if (isset($usedFixtures[$candidate->fixture_id])) {
                        continue;
                    }

                    $odds = $this->effectiveOdds($candidate);

                    // Enforce a minimum odds per leg.
                    if ($odds === null || $odds < self::MIN_ODDS) {
                        continue;
                    }

                    $usedFixtures[$candidate->fixture_id] = true;

                    $legs[] = [
                        'fixture' => $candidate->fixture,
                        'prediction' => $candidate,
                        'confidence' => (int) $candidate->confidence,
                        'odds' => $odds,
                        'odds_source' => ($candidate->odds && (float) $candidate->odds > 1) ? 'bookmaker' : 'model',
                    ];

                    $totalOdds *= $odds;
                    $progressed = true;
                    break;
                }
            }

            if (! $progressed) {
                break;
            }
        }

        return [
            'ticket_number' => $number,
            'target_odds' => $targetOdds,
            'legs' => $legs,
            'total_odds' => round($totalOdds, 2),
            'avg_confidence' => $legs ? round(array_sum(array_column($legs, 'confidence')) / count($legs), 1) : null,
            'combined_probability' => $this->combinedProbability($legs),
            'reached_target' => $totalOdds >= $targetOdds,
        ];
    }

    /**
     * Bookmaker odds when available, otherwise model-implied fair odds
     * (100 / probability). Clearly labelled via 'odds_source'.
     */
    protected function effectiveOdds(Prediction $prediction): ?float
    {
        $bookmakerOdds = (float) $prediction->odds;

        if ($bookmakerOdds > 1) {
            return round($bookmakerOdds, 2);
        }

        $probability = (float) ($prediction->calibrated_probability ?? $prediction->probability ?? 0);

        if ($probability <= 0 || $probability >= 100) {
            return null;
        }

        return round(100 / $probability, 2);
    }

    /**
     * Product of per-leg calibrated probabilities (honest combined win chance).
     *
     * @param list<array<string,mixed>> $legs
     */
    protected function combinedProbability(array $legs): ?float
    {
        if (empty($legs)) {
            return null;
        }

        $probability = 1.0;

        foreach ($legs as $leg) {
            $p = (float) ($leg['prediction']->calibrated_probability ?? $leg['prediction']->probability ?? 0);

            if ($p <= 0) {
                return null;
            }

            $probability *= $p / 100;
        }

        return round($probability * 100, 1);
    }
}
