# Prediction Evaluation & Backtesting (Phase 1E)

This document explains the architecture of Esurebet's historical prediction
evaluation system. The system's purpose is to **measure** accuracy — it never
guarantees accuracy, and it never tunes the live model to make historical
numbers look better.

## Architecture Overview

```
Live predictions (predictions table)
        │
        │  resolved by
        ▼
PredictionResultService ──► writes result / actual_score / model_result /
                            override_result / resolved_at / result_corrections
        │
        ▼
PerformanceAnalyticsService (admin dashboard /admin/predictions/performance)

Backtest runs (backtest_runs table)
        │
        │  processed by
        ▼
RunBacktestJob ──► BacktestEngine ──► BacktestDataCollector (walk-forward)
        │                                  │
        │                                  ▼
        │                          FeatureEngine / Ensemble / Markets
        │                                  │
        ▼                                  ▼
backtest_predictions table          (identical math, leak-free inputs)
```

Live and backtest predictions are stored in **separate tables** and are never
mixed. Backtesting cannot touch live predictions, published predictions,
admin overrides, or homepage selections.

## Result Resolution

`App\Services\Prediction\Evaluation\MarketResultResolver` resolves a single
selection against a final score using **structured market codes**, not text
matching:

| Market          | WON when                                    |
|-----------------|---------------------------------------------|
| `1x2`           | home > away / home == away / home < away    |
| `draw`          | home == away                                |
| `double_chance` | `1x` home>=away, `x2` home<=away, `12` home!=away |
| `over_1_5`      | total goals > 1 (or <= 1 for under)         |
| `over_2_5`      | total goals > 2 (or <= 2 for under)         |
| `btts`          | both teams score ≥ 1 (yes) or not (no)      |
| `correct_score` | exact scoreline match, no partial credit    |

`PredictionResultService` classifies each fixture as **pending** (not yet
finished / score not synced), **void** (postponed, cancelled, abandoned), or
**terminal**, and writes outcomes:

- `result` — the effective outcome (admin override wins if present)
- `model_result` — the original model selection's outcome
- `override_result` — the admin override selection's outcome
- `actual_score`, `resolved_at`, `void_reason`, `result_corrections`

**Immutability:** once resolved, a prediction is never silently overwritten.
If API-Football corrects an official result, the new outcome is stored and the
original is preserved in `result_corrections` (an append-only JSON audit trail).

## Backtesting Engine & Walk-Forward Evaluation

`BacktestEngine` runs a `BacktestRun` over completed historical fixtures stored
in the `fixtures` table.

`BacktestDataCollector` builds the `PredictionContext` **only** from fixtures
whose `match_date` is strictly before the fixture being predicted (walk-forward
evaluation). It never calls the live API-Football client, which would return
*current* form and leak future information.

- Form is computed from the team's previous completed matches.
- Team stats (goals for/against home & away, clean sheets, BTTS) are computed
  from previous league+season matches.
- Head-to-head and standings are computed from previous matches only.
- Historical odds / API-AI predictions / injuries are **not stored**, so those
  models report themselves unavailable instead of inventing values.

## Data Leakage Protection

The mandatory leakage scenario is enforced by tests:

- Fixture A: January 1
- Fixture B: January 8
- Fixture C: January 15

Predicting B must not use C; predicting C may use B. See
`tests/Feature/WalkForwardDataLeakageTest.php`.

## Metrics

`App\Services\Prediction\Evaluation\MetricsCalculator` implements:

- **Accuracy** — won / (won + lost); voids excluded.
- **Brier score** — `mean((p - y)^2)`, `p ∈ [0,1]`; lower is better.
- **Log loss** — binary cross-entropy with clipping to avoid `log(0)`.
- **Calibration** — predicted probability vs actual success frequency per bucket.
- **Confidence buckets** — 50-59, 60-69, 70-79, 80-89, 90-100.
- **Selectivity** — all vs 70+ vs 80+ vs 90+ confidence (coverage always shown).
- **Coverage** — predicted fixtures / eligible fixtures.

## Statistical Honesty

- Every accuracy figure is accompanied by its sample size.
- Rankings respect `evaluation.minimum_sample_size` (default 100); below that
  the UI shows "Insufficient sample size".
- Losses, voids, NO_BET selections and coverage are never hidden.
- **ROI** requires historical odds. This database does not store historical
  odds, so ROI is displayed as "N/A" rather than fabricated.

## Commands & Scheduling

- `php artisan predictions:resolve-results` — resolve completed fixtures
  (idempotent). Scheduled every 5 minutes in the evening and nightly.
- `php artisan predictions:backtest` — run a backtest synchronously from CLI.

Admin-initiated backtests are queued via `App\Jobs\RunBacktestJob`.

## Backtest Reproducibility

Each `BacktestRun` stores `model_version`, league, season, date range, markets,
confidence/probability thresholds, and a full `config_snapshot` (ensemble
weights, Poisson config, home advantage, confidence weights, NO_BET
thresholds). A completed run is reproducible even if the active model
configuration changes later.

## Safety

- Backtests never modify live predictions, published predictions, admin
  overrides, or public homepage selections.
- Backtests are isolated in `backtest_runs` / `backtest_predictions`.
- Result resolution is idempotent — running it repeatedly never duplicates.
- Completed backtests are archived, not hard-deleted.
