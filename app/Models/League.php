<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_football_league_id',
        'name',
        'slug',
        'country',
        'logo',
        'season',
        'enabled',
        'prediction_enabled',
        'homepage_enabled',
        'priority',
        'prediction_min_confidence',
        'prediction_min_probability',
        'auto_publish',
    ];

    protected function casts(): array
    {
        return [
            'season' => 'integer',
            'enabled' => 'boolean',
            'prediction_enabled' => 'boolean',
            'homepage_enabled' => 'boolean',
            'priority' => 'integer',
            'prediction_min_confidence' => 'integer',
            'prediction_min_probability' => 'integer',
            'auto_publish' => 'boolean',
        ];
    }

    /**
     * Predictions belonging to this league (joined by API-Football league id).
     */
    public function predictions()
    {
        return $this->hasMany(\App\Models\Prediction::class, 'league_id', 'api_football_league_id');
    }

    /**
     * Fixtures belonging to this league (joined by API-Football league id).
     */
    public function fixtures()
    {
        return $this->hasMany(\App\Models\Fixture::class, 'league_id', 'api_football_league_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopePredictionEnabled($query)
    {
        return $query->where('enabled', true)->where('prediction_enabled', true);
    }

    public function scopeHomepageEnabled($query)
    {
        return $query->where('enabled', true)->where('homepage_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderBy('name');
    }
}
