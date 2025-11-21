<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'date',
        'odds',
        'status',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
        'odds' => 'decimal:2',
    ];
}
