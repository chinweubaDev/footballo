<?php

namespace App\Services\Prediction\Admin;

use App\Models\League;
use App\Models\LeagueMarketGate;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1I — deterministic publication decision engine.
 *
 * Single source of truth for whether a generated prediction becomes
 * PUBLISHED, NO_BET, REJECTED or PENDING_REVIEW, with a recorded reason.
 *
 * Gate precedence (most specific wins):
 *   league + market  >  market  >  league  >  global  >  system default.
 */
class PredictionPublicationService
{
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_NO_BET = 'no_bet';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING_REVIEW = 'pending_review';

    public function __construct(protected AuditLogger $audit)
    {
    }

    /**
     * Resolve the effective publication gate for a league + market.
     *
     * @return array{min_probability:int,min_confidence:int,min_data_quality:int,enabled:bool,source:string}
     */
    public function resolveGate(?League $league, PredictionCategory $category): array
    {
        $override = $league
            ? LeagueMarketGate::query()
                ->where('league_id', $league->api_football_league_id)
                ->where('market_code', $category->code)
                ->first()
            : null;

        $source = 'global';
        $enabled = true;
        $minProbability = null;
        $minConfidence = null;

        if ($override !== null) {
            $source = 'league_market';
            $enabled = $override->enabled;
            $minProbability = $override->min_probability;
            $minConfidence = $override->min_confidence;
        }

        // Market-level (category) fallback.
        if ($minProbability === null && $category->min_probability !== null) {
            $minProbability = $category->min_probability;
            $source = $source === 'global' ? 'market' : $source;
        }

        if ($minConfidence === null && $category->min_confidence !== null) {
            $minConfidence = $category->min_confidence;
            $source = $source === 'global' ? 'market' : $source;
        }

        // League-level fallback.
        if ($minProbability === null && $league?->prediction_min_probability !== null) {
            $minProbability = $league->prediction_min_probability;
            $source = $source === 'global' ? 'league' : $source;
        }

        if ($minConfidence === null && $league?->prediction_min_confidence !== null) {
            $minConfidence = $league->prediction_min_confidence;
            $source = $source === 'global' ? 'league' : $source;
        }

        // Global fallback.
        if ($minProbability === null) {
            $minProbability = config('prediction.no_bet.min_probability', 70);
        }

        if ($minConfidence === null) {
            $minConfidence = config('prediction.min_confidence', 75);
        }

        return [
            'min_probability' => (int) $minProbability,
            'min_confidence' => (int) $minConfidence,
            'min_data_quality' => (int) config('prediction.no_bet.min_data_quality', 65),
            'enabled' => (bool) $enabled,
            'source' => $source,
        ];
    }

    /**
     * Decide a prediction's publication status without writing.
     *
     * @return array{status:string,reason:string}
     */
    public function decide(Prediction $prediction): array
    {
        $league = $prediction->league;
        $category = $prediction->market_code
            ? PredictionCategory::query()->where('code', $prediction->market_code)->first()
            : null;
        $model = $prediction->model;

        if ($league && (! $league->enabled || ! $league->prediction_enabled)) {
            return ['status' => self::STATUS_REJECTED, 'reason' => 'league disabled'];
        }

        if ($category && ! $category->enabled) {
            return ['status' => self::STATUS_REJECTED, 'reason' => 'market disabled'];
        }

        if ($model && ! $model->active) {
            return ['status' => self::STATUS_REJECTED, 'reason' => 'model not active'];
        }

        $gate = $this->resolveGate($league, $category);

        if (! $gate['enabled']) {
            return ['status' => self::STATUS_REJECTED, 'reason' => 'league x market disabled'];
        }

        $probability = (float) ($prediction->calibrated_probability ?? $prediction->probability ?? 0);
        $confidence = (int) $prediction->confidence;
        $dataQuality = (int) ($prediction->data_quality_score ?? 0);

        if ($probability < $gate['min_probability']) {
            return ['status' => self::STATUS_NO_BET, 'reason' => 'probability below gate'];
        }

        if ($confidence < $gate['min_confidence']) {
            return ['status' => self::STATUS_NO_BET, 'reason' => 'confidence below gate'];
        }

        if ($dataQuality < $gate['min_data_quality']) {
            return ['status' => self::STATUS_NO_BET, 'reason' => 'data quality below gate'];
        }

        $autoPublish = $league?->auto_publish ?? (bool) config('prediction.auto_publish', true);

        return [
            'status' => $autoPublish ? self::STATUS_PUBLISHED : self::STATUS_PENDING_REVIEW,
            'reason' => $autoPublish ? 'passed gate' : 'passed gate; auto-publish disabled',
        ];
    }

    /**
     * Decide and persist the publication outcome + provenance.
     */
    public function apply(Prediction $prediction): Prediction
    {
        $decision = $this->decide($prediction);
        $league = $prediction->league;
        $category = $prediction->market_code
            ? PredictionCategory::query()->where('code', $prediction->market_code)->first()
            : null;

        $gate = $this->resolveGate($league, $category);

        DB::transaction(function () use ($prediction, $decision, $gate) {
            $prediction->update([
                'status' => $decision['status'],
                'publication_reason' => $decision['reason'],
                'gate_probability' => $gate['min_probability'],
                'gate_confidence' => $gate['min_confidence'],
                'configuration_version' => $this->configurationVersion(),
                'published_at' => $decision['status'] === self::STATUS_PUBLISHED ? now() : null,
            ]);

            $this->audit->log($prediction, 'publication_decision', [
                'status' => $decision['status'],
                'reason' => $decision['reason'],
                'gate' => $gate,
            ]);
        });

        return $prediction->fresh();
    }

    /**
     * A reproducible hash of the publication-relevant configuration, so a
     * historical publication decision stays explainable after settings change.
     */
    public function configurationVersion(): string
    {
        $payload = [
            'prediction' => config('prediction'),
            'categories' => PredictionCategory::query()
                ->orderBy('id')
                ->get(['code', 'enabled', 'min_probability', 'min_confidence'])
                ->toArray(),
            'league_market_gates' => LeagueMarketGate::query()
                ->orderBy('id')
                ->get(['league_id', 'market_code', 'enabled', 'min_probability', 'min_confidence'])
                ->toArray(),
        ];

        return substr(md5(json_encode($payload)), 0, 12);
    }
}
