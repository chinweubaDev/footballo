# Phase 1P — Multi-Season Validation Methodology

## Data separation (mandatory)

- **BACKTEST**: `backtest_runs` + `backtest_predictions`. Never touches live/settled predictions.
- **LIVE**: `predictions` table (production v1.0.0 + v1.1.0 shadow).
- **EXPERIMENT**: a `PredictionModel` row with a non-promoted `version` + `configuration` (JSON), evaluated only via `BacktestRun`.

Never mix these labels in reporting.

## Multi-season datasets

Seasons 2023 / 2024 / 2025 imported from API-Football via
`php artisan predictions:backfill --league=<id> --season=<year>`.
Deduplication is by unique `api_fixture_id` (`updateOrCreate`). Incomplete
seasons are marked incomplete — never treated as complete.

## Walk-forward leakage guards

- `BacktestDataCollector::warm($league, $season)` scopes the dataset to a single
  league+season; `formList`/`teamStats`/`h2h`/`standings` only read matches with
  `match_date < kickoff` (strictly before the predicted fixture).
- `feature_data_timestamp <= prediction_generated_at < kickoff` is enforced in
  live settlement by `FeatureProvenanceService`.
- Regression tests: `WalkForwardDataLeakageTest`, `SeasonIsolationTest`.

## Train / validation / test separation

For any future candidate model optimization:
- TRAIN: 2023
- VALIDATE: 2024
- TEST (out-of-sample): 2025

Or rolling walk-forward windows. Never evaluate on the same data used to tune.

## Pooled accuracy

Pooled accuracy = **total wins ÷ total resolved** across the pool — never a
blind average of per-season percentages. (A 90% season with n=100 and a 5%
season with n=200 pool to 33.3%, not 47.5%.)

## Experiment framework

- A candidate is a `PredictionModel` with `version` (e.g. "v1.1.x-experiment")
  and `configuration` (JSON), never called v1.2.0 until validated.
- `BacktestRun` records `model_version`, `config_snapshot`, `markets`, season
  and metrics; `BacktestPrediction` records per-fixture predictions.
- `php artisan predictions:ablate` measures per-feature impact (form / h2h /
  team_strength / xg).
- Promotion gate lives in `ModelLifecycleService` — requires approved + ≥500
  resolved shadow predictions + admin approval. Never automatic.
