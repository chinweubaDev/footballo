<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'enabled',
        'min_confidence',
        'min_probability',
        'minimum_sample_size',
        'gate_status',
        'homepage_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'homepage_enabled' => 'boolean',
            'min_confidence' => 'integer',
            'min_probability' => 'integer',
            'minimum_sample_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeHomepageEnabled($query)
    {
        return $query->where('enabled', true)->where('homepage_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Effective probability threshold: market override, else global default.
     */
    public function getEffectiveMinProbabilityAttribute(): int
    {
        return $this->min_probability ?? (int) config('prediction.no_bet.min_probability', 70);
    }
}
