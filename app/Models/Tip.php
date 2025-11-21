<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    protected $fillable = [
        'title', 'content', 'type', 'category', 'fixture_id', 'league_name', 'home_team', 
        'away_team', 'match_date', 'match_time', 'prediction', 'odds', 
        'status', 'is_featured', 'is_active'
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime:H:i',
        'odds' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Scope for VIP tips
    public function scopeVip($query)
    {
        return $query->where('type', 'vip');
    }

    // Scope for VVIP tips
    public function scopeVvip($query)
    {
        return $query->where('type', 'vvip');
    }

    // Scope for active tips
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured tips
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Get status badge color
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'won' => 'green',
            'lost' => 'red',
            'void' => 'yellow',
            default => 'blue'
        };
    }

    // Get type badge color
    public function getTypeBadgeColorAttribute()
    {
        return match($this->type) {
            'vip' => 'blue',
            'vvip' => 'purple',
            default => 'gray'
        };
    }
}
