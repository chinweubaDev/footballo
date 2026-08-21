<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Evaluation / Backtesting configuration
    |--------------------------------------------------------------------------
    |
    | This file controls the historical prediction evaluation system
    | (Phase 1E). It does NOT tune the live prediction engine; its only job is
    | to MEASURE how well predictions actually performed.
    |
    */

    /*
    | A performance claim (league, market, confidence bucket, etc.) is only
    | considered meaningful once at least this many resolved predictions
    | exist. Below this, the UI shows "Insufficient sample size".
    */
    'minimum_sample_size' => (int) env('EVAL_MIN_SAMPLE_SIZE', 100),

    /*
    | Fixture statuses that represent a finished match whose result is final.
    */
    'terminal_statuses' => ['FT', 'AET', 'PEN'],

    /*
    | Fixture statuses that represent a match with no valid result.
    | Predictions on these fixtures are resolved as VOID.
    */
    'void_statuses' => ['PST', 'CANC', 'ABD', 'SUSP', 'WO', 'AWD'],

    /*
    | Confidence score buckets (inclusive lower bound, exclusive upper bound).
    | The final element is the inclusive ceiling for the last bucket.
    */
    'confidence_buckets' => [50, 60, 70, 80, 90, 100],

    /*
    | Probability buckets (%). Same semantics as confidence buckets.
    */
    'probability_buckets' => [50, 60, 70, 80, 90, 100],

    /*
    | Brier score: mean((predicted_probability - actual_outcome)^2), lower is
    | better (0 = perfect). Only meaningful for binary events.
    */
    'brier' => [
        'enabled' => true,
    ],

    /*
    | Log loss (binary cross-entropy). Probabilities are clipped into
    | [epsilon, 1 - epsilon] to protect against log(0).
    */
    'log_loss' => [
        'enabled' => true,
        'epsilon' => 1e-12,
    ],

    /*
    | ROI simulation defaults. ROI is ONLY computed when historical odds are
    | available; it is never fabricated.
    */
    'roi' => [
        'stake' => (float) env('EVAL_ROI_STAKE', 1000),
        'currency' => env('EVAL_ROI_CURRENCY', 'NGN'),
    ],

    /*
    | Walk-forward form window: the number of previous matches used to compute
    | a team's form at prediction time.
    */
    'walk_forward' => [
        'form_matches' => (int) env('EVAL_FORM_MATCHES', 10),
    ],

    /*
    | Cache TTL (seconds) for computed performance summaries. Summaries are
    | invalidated whenever new results are resolved or a backtest completes.
    */
    'cache_ttl' => (int) env('EVAL_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Model selection gate (Phase 1G)
    |--------------------------------------------------------------------------
    |
    | A candidate model may only be activated once all configured checks pass.
    | These thresholds make the approval rule explicit and auditable.
    */
    'model_gate' => [
        // Minimum resolved shadow predictions before activation is allowed.
        'minimum_shadow_predictions' => (int) env('EVAL_MIN_SHADOW_PREDICTIONS', 500),

        // Minimum resolved predictions per market before a market comparison counts.
        'minimum_sample_per_market' => (int) env('EVAL_MIN_SAMPLE_PER_MARKET', 100),

        // Calibration must not be materially worse (Brier absolute delta).
        'max_brier_worsening' => (float) env('EVAL_MAX_BRIER_WORSENING', 0.03),

        // Accuracy must not degrade more than this many points per market.
        'max_accuracy_degradation' => (float) env('EVAL_MAX_ACCURACY_DEGRADATION', 5.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Generalization score (Phase 1G)
    |--------------------------------------------------------------------------
    |
    | A composite model evaluation score. Accuracy is NOT the whole score.
    |   score = 0.30*accuracy_norm + 0.25*(1-brier) + 0.20*calibration
    |         + 0.15*coverage + 0.10*consistency
    | where consistency is based on the spread (standard deviation) of
    | per-league accuracy. Higher is better.
    */
    'generalization' => [
        'accuracy_weight' => 0.30,
        'brier_weight' => 0.25,
        'calibration_weight' => 0.20,
        'coverage_weight' => 0.15,
        'consistency_weight' => 0.10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Market & league status classification (Phase 1G §25/26)
    |--------------------------------------------------------------------------
    |
    | Maps measured performance into a human label. The labels are DERIVED
    | from validation data only — they are never hardcoded per market/league.
    |
    |   INSUFFICIENT_DATA  resolved < minimum_sample
    |   WEAK               accuracy < weak_accuracy
    |   NEUTRAL            weak_accuracy <= accuracy < promising_accuracy
    |   PROMISING          promising_accuracy <= accuracy < strong_accuracy
    |   STRONG             accuracy >= strong_accuracy AND brier <= strong_brier_max
    */
    'status_classification' => [
        'minimum_sample' => (int) env('EVAL_STATUS_MIN_SAMPLE', 100),
        'strong_accuracy' => (float) env('EVAL_STATUS_STRONG_ACCURACY', 62.0),
        'promising_accuracy' => (float) env('EVAL_STATUS_PROMISING_ACCURACY', 55.0),
        'weak_accuracy' => (float) env('EVAL_STATUS_WEAK_ACCURACY', 50.0),
        'strong_brier_max' => (float) env('EVAL_STATUS_STRONG_BRIER_MAX', 0.23),
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistical significance (Phase 1G §13)
    |--------------------------------------------------------------------------
    |
    | Model comparison uses a two-proportion z-test. A difference is only
    | reported as an improvement/regression when it is statistically
    | significant AND the sample sizes are sufficient; otherwise the UI shows
    | "Insufficient evidence of improvement."
    */
    'significance' => [
        'alpha' => (float) env('EVAL_SIGNIFICANCE_ALPHA', 0.05),
        // Minimum sample per version before a comparison is even attempted.
        'minimum_sample' => (int) env('EVAL_SIGNIFICANCE_MIN_SAMPLE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data drift detection (Phase 1G §38)
    |--------------------------------------------------------------------------
    |
    | Tracks the distribution of league-level features over time (scoring rate,
    | home-win rate, over-2.5 rate, BTTS rate) and flags DATA DRIFT when a
    | window's value moves materially relative to the baseline window.
    */
    'data_drift' => [
        // Chronological window size in months.
        'window_months' => (int) env('EVAL_DRIFT_WINDOW_MONTHS', 3),
        // Minimum fixtures per window before it is considered measurable.
        'minimum_fixtures' => (int) env('EVAL_DRIFT_MIN_FIXTURES', 30),
        // Relative change (percentage points) that flags a drift.
        'drift_threshold_pct' => (float) env('EVAL_DRIFT_THRESHOLD_PCT', 5.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Publication gate optimizer (Phase 1G.1)
    |--------------------------------------------------------------------------
    |
    | Sweeps a 2D grid of (probability × confidence) thresholds per market and
    | recommends a gate. The recommendation NEVER auto-applies and is chosen
    | with a documented composite score — accuracy is not the only criterion.
    |
    | Sample-size labels (configurable):
    |   n < insufficient_sample_threshold  => INSUFFICIENT SAMPLE
    |   n < minimum_sample_size            => LOW SAMPLE
    |   n >= minimum_sample_size           => SUFFICIENT SAMPLE
    |
    | Recommendation rule: consider only grid points where
    |   n >= minimum_sample_size AND coverage >= minimum_coverage
    |   AND accuracy >= minimum_accuracy AND brier <= max_brier;
    | among those, pick the one maximising
    |   score = w_acc*(acc/100) + w_brier*(1 - brier) + w_cov*(coverage/100).
    */
    'gate_optimizer' => [
        'probability_thresholds' => [50, 55, 60, 65, 70, 75, 80, 85, 90],
        'confidence_thresholds' => [50, 55, 60, 65, 70, 75, 80, 85, 90],
        'insufficient_sample_threshold' => (int) env('EVAL_GATE_INSUFFICIENT_SAMPLE', 50),
        'minimum_sample_size' => (int) env('EVAL_GATE_MIN_SAMPLE', 100),
        'minimum_coverage' => (float) env('EVAL_GATE_MIN_COVERAGE', 10.0),
        'minimum_accuracy' => (float) env('EVAL_GATE_MIN_ACCURACY', 60.0),
        'max_brier' => (float) env('EVAL_GATE_MAX_BRIER', 0.30),
        'weights' => [
            'accuracy' => (float) env('EVAL_GATE_W_ACCURACY', 0.45),
            'brier' => (float) env('EVAL_GATE_W_BRIER', 0.30),
            'coverage' => (float) env('EVAL_GATE_W_COVERAGE', 0.25),
        ],
    ],
];
