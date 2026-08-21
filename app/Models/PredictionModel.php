<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a stored prediction model version (e.g. "v1.0.0").
 *
 * Model lifecycle states (Phase 1G):
 *   candidate → shadow → approved → active → retired
 *                         (rejected can occur at any point before active)
 */
class PredictionModel extends Model
{
    use HasFactory;

    public const STATUS_CANDIDATE = 'candidate';
    public const STATUS_SHADOW = 'shadow';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RETIRED = 'retired';

    protected $fillable = [
        'name',
        'version',
        'description',
        'configuration',
        'active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'active' => 'boolean',
        ];
    }

    public function predictions()
    {
        return $this->hasMany(\App\Models\Prediction::class, 'model_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(ModelAuditLog::class, 'prediction_model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getIsProductionAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
