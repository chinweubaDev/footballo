<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'api_football' => [
        'key' => env('FOOTBALL_API_KEY', env('API_FOOTBALL_KEY')),
        'base_url' => env('FOOTBALL_API_BASE_URL', 'https://v3.football.api-sports.io'),
        // Phase 1K — retry/backoff for transient API failures.
        'retry' => [
            'max_attempts' => (int) env('FOOTBALL_API_MAX_ATTEMPTS', 4),
            'base_delay' => (int) env('FOOTBALL_API_BASE_DELAY', 1),
            'max_delay' => (int) env('FOOTBALL_API_MAX_DELAY', 30),
            // Transient statuses that are worth retrying.
            'transient_statuses' => [408, 429, 500, 502, 503, 504],
        ],
    ],

    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
    ],

    'newsapi' => [
        'key' => env('NEWSAPI_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue health (Phase 1N)
    |--------------------------------------------------------------------------
    |
    | Thresholds used by the /admin/system/queue dashboard to classify the
    | queue as HEALTHY / WARNING / FAILED.
    */
    'queue_health' => [
        // Any unresolved critical event within this window => FAILED.
        'critical_window_minutes' => (int) env('QUEUE_HEALTH_CRITICAL_MINUTES', 15),
        // Any failed job within this window => WARNING.
        'failed_window_hours' => (int) env('QUEUE_HEALTH_FAILED_HOURS', 24),
        // Pending jobs above this => WARNING (backlog).
        'pending_warning_threshold' => (int) env('QUEUE_HEALTH_PENDING_WARNING', 1000),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),  // e.g. @esurebettips or -1001234567890
    ],

];
