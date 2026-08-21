<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prediction Engine
    |--------------------------------------------------------------------------
    |
    | Central configuration for the Esurebet prediction engine. These values
    | are environment-driven and must never be exposed to the frontend.
    |
    */

    'enabled' => env('PREDICTION_ENABLED', true),

    'auto_publish' => env('PREDICTION_AUTO_PUBLISH', true),

    'min_confidence' => (int) env('PREDICTION_MIN_CONFIDENCE', 75),

    'min_probability' => (int) env('PREDICTION_MIN_PROBABILITY', 70),

    'lookahead_days' => (int) env('PREDICTION_LOOKAHEAD_DAYS', 7),

    'default_season' => (int) env('PREDICTION_DEFAULT_SEASON', 2025),

    'model_version' => env('PREDICTION_MODEL_VERSION', 'v1.0.0'),

    'ai' => [
        'enabled' => env('AI_ENABLED', false),
        'provider' => env('AI_PROVIDER'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ensemble model weights
    |--------------------------------------------------------------------------
    |
    | Starting weights for the ensemble blend. These are not guaranteed to be
    | optimal — they are a documented, configurable starting point that must
    | sum to 1.0. They can be overridden per model version via the active
    | prediction_models.configuration JSON.
    |
    */

    'ensemble' => [
        'weights' => [
            'poisson' => 0.30,
            'form' => 0.20,
            'home_away' => 0.15,
            'team_strength' => 0.15,
            'api_football' => 0.10,
            'odds' => 0.10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Poisson model
    |--------------------------------------------------------------------------
    */

    'poisson' => [
        'max_goals' => 6,
        'league_home_goals_default' => 1.45,
        'league_away_goals_default' => 1.15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Home advantage
    |--------------------------------------------------------------------------
    |
    | Fallback baseline goal advantage used when league-specific home/away data
    | is not available. League-specific data takes precedence when present.
    |
    */

    'home_advantage' => [
        'baseline' => 0.25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Confidence engine
    |--------------------------------------------------------------------------
    */

    'confidence' => [
        'levels' => [
            'low' => 50,
            'moderate' => 65,
            'high' => 80,
        ],
        'weights' => [
            'probability_strength' => 30,
            'model_agreement' => 25,
            'data_quality' => 20,
            'market_agreement' => 15,
            'form_consistency' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | NO BET policy
    |--------------------------------------------------------------------------
    |
    | Global fallback thresholds. League-specific prediction_min_confidence and
    | category min_confidence take precedence (see PredictionEngine).
    |
    */

    'no_bet' => [
        'min_probability' => 70,
        'min_confidence' => 75,
        'min_data_quality' => 65,
    ],

];
