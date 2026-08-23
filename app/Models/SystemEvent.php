<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 1K — a persistent system event / alert.
 */
class SystemEvent extends Model
{
    use HasFactory;

    public const SEVERITY_INFO = 'INFO';
    public const SEVERITY_WARNING = 'WARNING';
    public const SEVERITY_ERROR = 'ERROR';
    public const SEVERITY_CRITICAL = 'CRITICAL';

    protected $fillable = [
        'type',
        'severity',
        'message',
        'context',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }
}
