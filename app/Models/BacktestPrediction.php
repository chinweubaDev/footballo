<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single prediction produced by a backtest run. Backtest predictions are
 * kept completely separate from live predictions to avoid any contamination
 * of public homepage selections or admin overrides.
 */
class BacktestPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'backtest_run_id',
        'fixture_id',
        'market_code',
        'selection',
        'probability',
        'raw_probability',
        'calibrated_probability',
        'calibration_version',
        'confidence',
        'model_version',
        'data_quality_score',
        'prediction_data',
        'status',
        'result',
        'actual_score',
        'predicted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'decimal:2',
            'raw_probability' => 'decimal:2',
            'calibrated_probability' => 'decimal:2',
            'confidence' => 'integer',
            'data_quality_score' => 'integer',
            'prediction_data' => 'array',
            'predicted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function run()
    {
        return $this->belongsTo(BacktestRun::class, 'backtest_run_id');
    }

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('result');
    }
}
