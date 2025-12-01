<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_fixture_id',
        'league_name',
        'league_country',
        'league_logo',
        'league_flag',
        'league_id',
        'season',
        'round',
        'home_team',
        'away_team',
        'home_team_logo',
        'away_team_logo',
        'home_team_id',
        'away_team_id',
        'match_date',
        'venue_name',
        'venue_city',
        'status',
        'home_goals',
        'away_goals',
        'home_goals_halftime',
        'away_goals_halftime',
        'today_tip',
        'featured',
        'maxodds_tip',
        'is_vip',
        'is_vvip',
        'is_surepick',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'today_tip' => 'boolean',
            'featured' => 'boolean',
            'maxodds_tip' => 'boolean',
            'is_vip' => 'boolean',
            'is_vvip' => 'boolean',
            'is_surepick' => 'boolean',
        ];
    }
// App\Models\Fixture.php

public function scopeTodayTips($query)
{
    return $query->whereHas('predictions', function ($q) {
        $q->where('today_tip', true);
    });
}

public function scopeFeatured($query)
{
    return $query->whereHas('predictions', function ($q) {
        $q->where('featured', true);
    });
}

    /**
     * Get the predictions for this fixture.
     */
    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Scope for today's tips.
     */
    public function scopeTodayTipds($query)
    {
        return $query->where('today_tip', true);
    }

    /**
     * Scope for featured predictions.
     */
    public function scopeFeaturded($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for maxodds tips.
     */
    public function scopeMaxoddsTips($query)
    {
        return $query->where('maxodds_tip', true);
    }

    /**
     * Scope for fixtures by league.
     */
    public function scopeByLeague($query, $leagueId)
    {
        return $query->where('league_id', $leagueId);
    }

    /**
     * Scope for fixtures by date range.
     */
    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('match_date', [$from, $to]);
    }
    /**
     * Scope for VIP fixtures.
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Scope for VVIP fixtures.
     */
    public function scopeVvip($query)
    {
        return $query->where('is_vvip', true);
    }
}
