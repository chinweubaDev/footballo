<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 1K — one API request observation (rate-limit / health tracking).
 */
class ApiRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint',
        'status',
        'successful',
        'is_rate_limited',
        'remaining_quota',
        'duration_ms',
        'retries',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'is_rate_limited' => 'boolean',
            'remaining_quota' => 'integer',
            'duration_ms' => 'integer',
            'retries' => 'integer',
        ];
    }
}
