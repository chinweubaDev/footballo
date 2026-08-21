<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Immutable audit trail for publication-gate threshold changes (Phase 1G.1).
 */
class PredictionGateAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_category_id',
        'market_code',
        'action',
        'old_probability',
        'new_probability',
        'old_confidence',
        'new_confidence',
        'admin_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_probability' => 'integer',
            'new_probability' => 'integer',
            'old_confidence' => 'integer',
            'new_confidence' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(PredictionCategory::class, 'prediction_category_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
