<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single backtest run. Configuration is snapshotted at creation time so a
 * completed run remains reproducible even if the live model config changes.
 */
class BacktestRun extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'name',
        'league_id',
        'season',
        'date_start',
        'date_end',
        'markets',
        'min_confidence',
        'min_probability',
        'model_version',
        'config_snapshot',
        'status',
        'total_fixtures',
        'processed_fixtures',
        'generated_predictions',
        'resolved_predictions',
        'metrics',
        'error',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'markets' => 'array',
            'config_snapshot' => 'array',
            'metrics' => 'array',
            'date_start' => 'date',
            'date_end' => 'date',
            'min_confidence' => 'integer',
            'min_probability' => 'decimal:2',
            'total_fixtures' => 'integer',
            'processed_fixtures' => 'integer',
            'generated_predictions' => 'integer',
            'resolved_predictions' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function predictions()
    {
        return $this->hasMany(BacktestPrediction::class, 'backtest_run_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'api_football_league_id');
    }

    /**
     * Progress percentage (0-100) based on processed fixtures.
     */
    public function getProgressPercentAttribute(): int
    {
        if ($this->total_fixtures <= 0) {
            return 0;
        }

        return (int) round(min(100, $this->processed_fixtures / $this->total_fixtures * 100));
    }

    /**
     * Whether the run is in a final (non-mutable) state.
     */
    public function getIsFinishedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED], true);
    }
}
