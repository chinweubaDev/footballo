<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'category',
        'tip',
        'confidence',
        'odds',
        'analysis',
        'is_premium',
        'is_maxodds',
        'status',
        'today_tip_content',
        'featured_tip_content',
        'vip_tip_content',
        'vvip_tip_content',
        'surepick_tip_content',
        'maxodds_tip_content',
        'over15_tip_content',
        'over25_tip_content',
        'double_chance_tip_content',
        'bts_tip_content',
        'draw_tip_content',
        'market_code',
        'selection',
        'probability',
        'model_version',
        'model_id',
        'original_selection',
        'admin_selection',
        'override_reason',
        'overridden_by',
        'overridden_at',
        'featured',
        'featured_priority',
        'featured_until',
        'admin_featured',
        'locked_at',
        'published_at',
        'explanation',
        'explanation_status',
        'league_id',
        'data_quality_score',
        'prediction_data',
        'result',
        'actual_score',
        'resolved_at',
        'model_result',
        'override_result',
        'void_reason',
        'result_corrections',
    ];

    protected function casts(): array
    {
        return [
            'odds' => 'decimal:2',
            'is_premium' => 'boolean',
            'is_maxodds' => 'boolean',
            'probability' => 'decimal:2',
            'featured' => 'boolean',
            'admin_featured' => 'boolean',
            'featured_priority' => 'integer',
            'featured_until' => 'datetime',
            'locked_at' => 'datetime',
            'published_at' => 'datetime',
            'overridden_at' => 'datetime',
            'data_quality_score' => 'integer',
            'prediction_data' => 'array',
            'resolved_at' => 'datetime',
            'result_corrections' => 'array',
        ];
    }

    /**
     * Get the fixture that owns the prediction.
     */
    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    /**
     * Scope for premium predictions.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for maxodds predictions.
     */
    public function scopeMaxodds($query)
    {
        return $query->where('is_maxodds', true);
    }

    /**
     * Scope for predictions by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the League configuration (by API-Football league id).
     */
    public function league()
    {
        return $this->belongsTo(\App\Models\League::class, 'league_id', 'api_football_league_id');
    }

    /**
     * Get the prediction model version that produced this prediction.
     */
    public function model()
    {
        return $this->belongsTo(\App\Models\PredictionModel::class, 'model_id');
    }

    /**
     * Get the admin who overrode this prediction.
     */
    public function overriddenBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'overridden_by');
    }

    /**
     * Get all recorded overrides for this prediction.
     */
    public function overrides()
    {
        return $this->hasMany(\App\Models\PredictionOverride::class);
    }

    /**
     * Get the feature snapshots used to generate this prediction.
     */
    public function features()
    {
        return $this->hasMany(\App\Models\PredictionFeature::class);
    }

    /**
     * Scope for published predictions.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for 1X2 predictions (the only market allowed in Sure Picks).
     */
    public function scopeSurePick($query)
    {
        return $query->where('market_code', '1x2');
    }

    /**
     * Scope for featured predictions.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for predictions by market code.
     */
    public function scopeByMarket($query, $marketCode)
    {
        return $query->where('market_code', $marketCode);
    }

    /**
     * Scope for predictions by API-Football league id.
     */
    public function scopeByLeague($query, $leagueId)
    {
        return $query->where('league_id', $leagueId);
    }

    /**
     * The selection shown to users: the admin override when present,
     * otherwise the model selection.
     */
    public function getEffectiveSelectionAttribute(): ?string
    {
        return $this->admin_selection ?? $this->selection;
    }

    /**
     * Whether this prediction currently has an active admin override.
     */
    public function getIsOverriddenAttribute(): bool
    {
        return $this->admin_selection !== null;
    }

    /**
     * Human-readable reason a prediction was marked NO_BET.
     */
    public function getNoBetReasonAttribute(): ?string
    {
        if ($this->status !== 'no_bet') {
            return null;
        }

        $minProbability = (int) config('prediction.no_bet.min_probability', 70);
        $minConfidence = (int) config('prediction.no_bet.min_confidence', 75);
        $minDataQuality = (int) config('prediction.no_bet.min_data_quality', 65);

        if ((float) $this->probability < $minProbability) {
            return 'Probability below threshold';
        }

        if ((int) $this->confidence < $minConfidence) {
            return 'Confidence below threshold';
        }

        if ((int) $this->data_quality_score < $minDataQuality) {
            return 'Insufficient data';
        }

        return 'Below threshold';
    }

    /**
     * Whether this prediction's result has been resolved (WON/LOST/VOID).
     */
    public function getIsResolvedAttribute(): bool
    {
        return in_array($this->result, ['won', 'lost', 'void'], true);
    }

    /**
     * The result of the selection that was actually used/shown to users:
     * the admin override when present, otherwise the original model selection.
     */
    public function getEffectiveResultAttribute(): ?string
    {
        return $this->override_result ?? $this->model_result ?? $this->result;
    }
}
