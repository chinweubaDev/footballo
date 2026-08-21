<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionPerformance extends Model
{
    use HasFactory;

    protected $table = 'prediction_performance';

    protected $fillable = [
        'league_id',
        'market_code',
        'model_version',
        'period',
        'period_start',
        'period_end',
        'total',
        'won',
        'lost',
        'void',
        'accuracy',
        'roi',
        'yield',
        'avg_confidence',
        'calibration_error',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'calculated_at' => 'datetime',
            'total' => 'integer',
            'won' => 'integer',
            'lost' => 'integer',
            'void' => 'integer',
            'accuracy' => 'float',
            'roi' => 'float',
            'yield' => 'float',
            'avg_confidence' => 'float',
            'calibration_error' => 'float',
        ];
    }

    public function league()
    {
        return $this->belongsTo(\App\Models\League::class, 'league_id', 'api_football_league_id');
    }
}
