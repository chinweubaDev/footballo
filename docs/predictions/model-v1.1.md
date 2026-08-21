# Model v1.1.0 — Calibration & Validation (Phase 1F)

This document describes how the v1.0.0 ensemble was improved into the
v1.1.0 **calibrated candidate**. v1.0.0 remains active and untouched;
v1.1.0 operates in shadow mode until an admin explicitly activates it.

## Calibration

`App\Services\Prediction\Calibration\ProbabilityCalibrator` implements:

- **Platt scaling** — `sigmoid(a * logit(p) + b)`, fit via gradient descent.
- **Isotonic regression** — monotone piecewise mapping via
  pool-adjacent-violators.

Probabilities are calibrated **per market**, because Over 1.5 and Correct
Score do not share probability behaviour. Correct Score is treated as a
multiclass market and is **not** binary-calibrated.

## Walk-Forward (No Data Leakage)

`WalkForwardCalibrator` sorts resolved predictions chronologically and uses a
70/30 chronological split: calibration is fit only on the earlier 70%, and
evaluated on the later 30% (held-out). Validation-period results are never
used to fit calibration, weights, or thresholds.

## Confidence vs Probability

Probability answers "how likely is this outcome?"; confidence answers "how
reliable is this prediction given the available evidence?" They are kept
separate. Confidence is not calibrated — it is measured (bucket accuracy with
95% Wilson confidence intervals).

## Thresholds

`ThresholdOptimizer` sweeps candidate thresholds (60–90%) per market and
reports accuracy, coverage and Brier for each, flagging thresholds below
`evaluation.minimum_sample_size`. Thresholds are never auto-applied.

## Shadow Mode

`predictions:shadow` generates v1.1.0 predictions with `status = 'shadow'`
(never `published`), so they never appear on the public site. The public
service only ever reads `status = 'published'` (v1.0.0).

## Model Selection

- `v1.0.0` — ACTIVE, unchanged.
- `v1.1.0` — CANDIDATE (`active = false`), calibrated but not published.

An admin must explicitly activate v1.1.0 after reviewing the comparison at
`/admin/predictions/models/compare`.

## Held-Out Calibration Results (Premier League 2025/26)

Platt scaling on 266 train / 114 validation samples per market:

| Market | Raw Brier | Platt Brier | Raw ECE | Platt ECE |
|---|---|---|---|---|
| 1x2 | 0.2556 | 0.2501 | 7.82 | 11.45 |
| double_chance | 0.1956 | 0.1927 | 6.47 | 6.21 |
| draw | 0.1998 | 0.1979 | 0.00 | 0.00 |
| over_1_5 | 0.2006 | 0.1866 | 10.04 | 0.27 |
| over_2_5 | 0.2592 | 0.2500 | 10.25 | 11.38 |
| btts | 0.2567 | 0.2510 | 8.87 | 2.06 |

Brier improved for every market; calibration error improved for
double_chance, over_1_5 and btts but worsened for 1x2 and over_2_5 on this
small validation set.

## Data Quality

Historical odds, API-Football AI predictions, injuries and lineups are not
stored, so backtest data quality is capped at 60/100 (form + team stats +
home/away + H2H). This is reported at `/admin/predictions/models/data-quality`.

## Commands

- `php artisan predictions:calibrate` — train walk-forward calibration, store
  in v1.1.0.
- `php artisan predictions:shadow` — generate v1.1.0 shadow predictions.
