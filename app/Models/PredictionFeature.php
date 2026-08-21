<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_id',
        'fixture_id',
        'model_version',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function prediction()
    {
        return $this->belongsTo(\App\Models\Prediction::class);
    }

    public function fixture()
    {
        return $this->belongsTo(\App\Models\Fixture::class);
    }
}
