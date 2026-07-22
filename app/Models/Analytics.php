<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['api_team_id', 'name', 'logo', 'country', 'national'];
}

class Player extends Model
{
    protected $fillable = ['api_player_id', 'name', 'photo', 'nationality', 'age', 'position'];
}

class FixtureEvent extends Model
{
    protected $fillable = ['fixture_id', 'team_id', 'player_id', 'assist_player_id', 'type', 'detail', 'elapsed', 'extra'];
    
    public function fixture() { return $this->belongsTo(Fixture::class); }
}

class FixtureLineup extends Model
{
    protected $fillable = ['fixture_id', 'team_id', 'formation', 'start_xi', 'substitutes', 'coach_id', 'coach_name'];
    
    protected $casts = ['start_xi' => 'array', 'substitutes' => 'array'];
    
    public function fixture() { return $this->belongsTo(Fixture::class); }
}

class FixturePlayerStat extends Model
{
    protected $fillable = [
        'fixture_id', 'team_id', 'player_id', 'minutes', 'position', 'rating',
        'shots_total', 'shots_on', 'goals_scored', 'goals_conceded', 'assists', 'saves',
        'passes_total', 'passes_key', 'passes_accuracy',
        'tackles_total', 'tackles_blocks', 'tackles_interceptions',
        'duels_total', 'duels_won', 'dribbles_attempts', 'dribbles_success',
        'fouls_drawn', 'fouls_committed',
        'cards_yellow', 'cards_red',
        'penalty_scored', 'penalty_missed', 'penalty_saved',
    ];
    
    public function fixture() { return $this->belongsTo(Fixture::class); }
}

class FixtureTeamStat extends Model
{
    protected $fillable = [
        'fixture_id', 'team_id',
        'shots_on_goal', 'shots_off_goal', 'total_shots', 'blocked_shots',
        'shots_insidebox', 'shots_outsidebox', 'fouls', 'corner_kicks', 'offsides',
        'ball_possession', 'yellow_cards', 'red_cards', 'goalkeeper_saves',
        'total_passes', 'passes_accurate', 'passes_pct', 'expected_goals',
    ];
    
    public function fixture() { return $this->belongsTo(Fixture::class); }
}

class BettingOdds extends Model
{
    protected $fillable = [
        'fixture_id', 'bookmaker_name', 'bookmaker_id',
        'home_odds', 'draw_odds', 'away_odds',
        'over25_odds', 'under25_odds', 'over15_odds', 'under15_odds',
        'bts_yes_odds', 'bts_no_odds',
    ];
    
    public function fixture() { return $this->belongsTo(Fixture::class); }
}
