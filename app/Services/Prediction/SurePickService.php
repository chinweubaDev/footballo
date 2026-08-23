<?php

namespace App\Services\Prediction;

use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Models\PredictionModel;
use App\Models\PublicationCandidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 1I — Sure Picks selection engine.
 *
 * Selects the strongest eligible 1X2 predictions for the homepage. Eligibility
 * requires: published status, production (active) model, enabled league and
 * enabled 1X2 market, and an upcoming fixture. Ranking is NEVER by raw
 * probability alone — it blends calibrated probability, confidence and the
 * league x market historical reliability signal.
 *
 * If nothing qualifies, an empty collection is returned (the view renders the
 * "No qualifying Sure Pick currently available." message) — selections are
 * never fabricated.
 */
class SurePickService
{
    public function __construct()
    {
    }

    /**
     * @return Collection<int,Prediction>
     */
    public function surePicks(int $limit = 6): Collection
    {
        return Cache::remember('sure_picks:'.$limit, now()->addMinutes(5), function () use ($limit) {
            return $this->compute($limit);
        });
    }

    /**
     * @return Collection<int,Prediction>
     */
    public function compute(int $limit = 6): Collection
    {
        $activeModel = PredictionModel::query()->where('active', true)->first();
        $category = PredictionCategory::query()->where('code', '1x2')->where('enabled', true)->first();

        if ($activeModel === null || $category === null) {
            return collect();
        }

        return Prediction::query()
            ->where('status', 'published')
            ->where('market_code', '1x2')
            ->where('model_id', $activeModel->id)
            ->whereHas('league', fn ($q) => $q->where('enabled', true)->where('prediction_enabled', true))
            ->whereHas('fixture', fn ($q) => $q->whereIn('status', ['NS', 'TBD', 'PST']))
            ->with(['fixture', 'league'])
            ->get()
            ->map(fn (Prediction $p) => ['prediction' => $p, 'score' => $this->score($p)])
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('prediction')
            ->values();
    }

    /**
     * Deterministic composite rank: calibrated probability + confidence +
     * league x market reliability. Raw probability is never used alone.
     */
    protected function score(Prediction $prediction): float
    {
        $probability = (float) ($prediction->calibrated_probability ?? $prediction->probability ?? 0);
        $confidence = (int) $prediction->confidence;
        $reliability = $this->reliability($prediction); // 0..1

        return round($probability * 0.55 + $confidence * 0.30 + $reliability * 15.0, 4);
    }

    /**
     * Historical league x market reliability, derived from approved
     * publication candidates (never hardcoded).
     */
    protected function reliability(Prediction $prediction): float
    {
        $candidate = PublicationCandidate::query()
            ->where('league_id', $prediction->league_id)
            ->where('market_code', $prediction->market_code)
            ->where('model_version', $prediction->model_version)
            ->first();

        if ($candidate === null) {
            return 0.5;
        }

        if ($candidate->status !== PublicationCandidate::STATUS_APPROVED) {
            return 0.5;
        }

        $accuracy = (float) ($candidate->metrics['accuracy'] ?? 0);

        if ($accuracy >= 70) {
            return 1.0;
        }

        if ($accuracy >= 60) {
            return 0.75;
        }

        return 0.5;
    }
}
