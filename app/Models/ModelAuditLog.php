<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Immutable audit trail for prediction-model lifecycle changes.
 */
class ModelAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_model_id',
        'action',
        'from_status',
        'to_status',
        'admin_id',
        'reason',
    ];

    public function model()
    {
        return $this->belongsTo(PredictionModel::class, 'prediction_model_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
