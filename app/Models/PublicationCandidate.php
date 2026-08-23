<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A Phase 1G.2 publication candidate: a single League x Market x Model
 * combination that meets the evidence bar for public Sure Picks.
 *
 * Status is NEVER set automatically to approved/rejected — candidates are
 * marked CANDIDATE and require an explicit admin decision.
 */
class PublicationCandidate extends Model
{
    use HasFactory;

    public const STATUS_CANDIDATE = 'candidate';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'league_id',
        'market_code',
        'model_version',
        'status',
        'recommended_probability',
        'recommended_confidence',
        'metrics',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'recommended_probability' => 'integer',
            'recommended_confidence' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'api_football_league_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
