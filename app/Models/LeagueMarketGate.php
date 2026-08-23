<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A per-league, per-market publication gate override (Phase 1I).
 *
 * Precedence when resolving a gate:
 *   league + market  >  market  >  league  >  global  >  default.
 */
class LeagueMarketGate extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'market_code',
        'enabled',
        'min_probability',
        'min_confidence',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'min_probability' => 'integer',
            'min_confidence' => 'integer',
        ];
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'api_football_league_id');
    }
}
