<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_id',
        'original_selection',
        'new_selection',
        'original_probability',
        'new_probability',
        'reason',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'original_probability' => 'decimal:2',
            'new_probability' => 'decimal:2',
        ];
    }

    public function prediction()
    {
        return $this->belongsTo(\App\Models\Prediction::class);
    }

    public function admin()
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_id');
    }
}
