# Esurebet AI-Assisted Prediction Engine — Implementation Plan

> Status: PLANNING COMPLETE — ready for implementation handoff.
> App: Laravel 12 / PHP ^8.2. Queue=database, Cache=file, Redis dormant.

## TL;DR
Build a real statistical prediction platform on the existing Laravel 12 app, reusing
`ApiFootballServiceEnhanced`, `Fixture`/`Prediction`, `PredictionEngine`, `GeneratePredictions`,
`UpdateLiveScores`, `TipCategorizer`, and the existing homepage. Replace the monolithic
"probability = confidence" blend with a modular Poisson + ensemble + confidence pipeline, add
database-driven league/category config, per-market normalized predictions, admin controls,
public league/match pages, and honest result + performance tracking. Optional AI explanation
via a provider abstraction. Phased delivery.

## Confirmed decisions (from user)
- Phased delivery (Phase 1 core, Phase 2 AI, Phase 3 ML/backtest).
- Normalized per-market prediction rows + keep legacy single-row table for backward-compat.
- Secure the admin panel (register auth + admin middleware, add permission gates).
- Keep database queue + file cache (no Redis requirement).

## Existing system (verified findings)
- Laravel 12 / PHP ^8.2. No app/Http/Kernel.php — bootstrap/app.php (Laravel 11+ style).
- Scheduler in routes/console.php. Queue=database, Cache=file. Redis configured but dormant.
- API client: `App\Services\ApiFootballServiceEnhanced` (hardcoded https://v3.football.api-sports.io,
  key from `services.api_football.key` = env API_FOOTBALL_KEY, 30-min cache).
- Engine: `App\Services\PredictionEngine::predictFixture(Fixture): array` — single monolithic blend
  (odds 30%, form 25%, xG 20%, H2H 15%, API-AI 10%). No Poisson, confidence == max probability,
  no NO_BET. Outputs 1x2/double_chance/bts/over15/over25/over35/draw/correct_score/half_time/xg.
- Command `predictions:generate` hardcodes `$topLeagueIds`. One Prediction row per fixture (1x2
  headline) + `*_tip_content` text columns for other markets.
- Command `scores:update-live` resolves only 1X2 via string contains; writes Result rows.
- Models: `Fixture` (many boolean category flags; missing `elapsed` migration, missing `upcoming`
  scope), `Prediction` (category/tip/confidence/odds/analysis + tip_content columns; `result`/
  `actual_score` columns exist but not fillable).
- No League/Category models. Leagues = string columns on fixtures.
- Homepage `home.blade.php` has inline "Sure Picks Tips" + "Most Featured Selections";
  `HomeController@index` re-runs `predictFixture()` live (slow).
- Admin: `AdminController` + `Admin\AdminBlogController`; admin routes have NO middleware;
  `AdminMiddleware` exists (App\Http\Middleware\AdminMiddleware) but alias never registered.
  Missing admin Blade views: predictions, create-prediction, pricing, payment-methods, create-fixture.
- Tables already present: fixtures, predictions, prediction_logs, teams, players, fixture_events,
  fixture_lineups, fixture_player_stats, fixture_team_stats, betting_odds, tips, results, etc.
- League IDs (from existing topLeagueIds): PL=39, LaLiga=140, SerieA=135, Bundesliga=78,
  Ligue1=61, Eredivisie=88. Season default 2025, per-league configurable.
- Tests dir does not exist yet (phpunit.xml references tests/Unit + tests/Feature). No routes/api.php.

## Architecture decisions
- `config/prediction.php` as single source of engine config (env-driven).
- `config/services.php` api_football.key falls back FOOTBALL_API_KEY -> API_FOOTBALL_KEY.
- `PredictionModelInterface` for pluggable models (Poisson now; Elo/ML later).
- `ConfidenceEngine` produces 0-100 independent of probability. NO_BET when below threshold.
- Per-market normalized rows in `predictions` (fixture_id+market_code+model_version unique), while
  retaining legacy columns (`tip`, `category`, `*_tip_content`) for existing views.
- Homepage reads only published rows via `PredictionRepository` (cached), never computes live.
- Result resolution + performance computed by dedicated services with prediction_logs audit trail.

## Phase 1 — Core statistical engine + config + admin + public + results

### Phase 1A — Foundations (config, env, migrations, models, seeders)
1. Add `config/prediction.php` reading env: PREDICTION_ENABLED, PREDICTION_AUTO_PUBLISH,
   PREDICTION_MIN_CONFIDENCE (75), PREDICTION_MIN_PROBABILITY (70), PREDICTION_LOOKAHEAD_DAYS (7),
   AI_PROVIDER (null), AI_API_KEY, AI_MODEL, AI_ENABLED (false). Update `config/services.php`
   api_football key fallback + add `ai` block. Update .env.example with new keys.
2. New migrations:
   - `create_leagues_table` — id, api_football_league_id unique, name, slug unique, country, logo,
     season, enabled, prediction_enabled, homepage_enabled, priority, prediction_min_confidence,
     auto_publish, timestamps.
   - `create_prediction_categories_table` — id, name, slug, code, enabled, min_confidence,
     homepage_enabled, sort_order, timestamps.
   - `create_prediction_models_table` — id, name, version, description, configuration json, active, timestamps.
   - `create_prediction_overrides_table` — id, prediction_id fk, original_selection, new_selection,
     original_probability, new_probability, reason, admin_id, created_at.
   - `create_prediction_features_table` — id, prediction_id fk, fixture_id, model_version,
     features json, timestamps.
   - `create_prediction_performance_table` — id, league_id nullable, market_code, model_version,
     period, period_start, period_end, total, won, lost, void, accuracy, roi, yield, avg_confidence,
     calibration_error, calculated_at.
   - `add_prediction_engine_columns` — to `predictions`: market_code string nullable, selection string
     nullable, probability decimal(5,2) nullable, model_version string nullable default 'v1.0.0',
     model_id fk nullable, original_selection, admin_selection, override_reason, overridden_by,
     overridden_at, featured bool, featured_priority int, featured_until datetime, admin_featured bool,
     locked_at, published_at, explanation text, explanation_status string, league_id int nullable,
     data_quality_score int nullable. Backfill market_code='1x2' for legacy rows, then add
     UNIQUE index (fixture_id, market_code, model_version).
   - `add_slug_and_elapsed_to_fixtures` — `slug` string nullable unique, `elapsed` int nullable.
   - `add_log_columns_to_prediction_logs` — model_version, execution_time_ms, context json nullable.
3. New models: `League`, `PredictionCategory`, `PredictionModel`, `PredictionOverride`,
   `PredictionFeature`, `PredictionPerformance`. Update `Fixture` (add slug/elapsed fillable,
   add `league()` relation, add `scopeUpcoming`). Update `Prediction` (new fillable, casts, scopes:
   published/surePick/featured/byMarket/byLeague, relations to League + overrides + features).
4. Seeders: `LeagueSeeder` (6 leagues w/ IDs above, enabled=true, min_confidence=75, auto_publish=true),
   `PredictionCategorySeeder` (1X2, Over 1.5, Over 2.5, Double Chance, BTTS, Draw, Correct Score),
   `PredictionModelSeeder` (v1.0.0 active).

### Phase 1B — Statistical engine services (app/Services/Prediction/)
5. `PredictionModelInterface` — `predict(array $features): array` (home/draw/away + xG + market probs).
6. `FeatureEngine` — gathers/calculates features from API data: recent form (recency-weighted,
   home/away split), goals scored/conceded, attack/defence strength, home advantage, league position,
   H2H (capped weight), clean-sheet %, BTTS %, over/under history, API prediction, bookmaker odds,
   xG where available, data-quality flags. Persistable via `prediction_features`.
7. `TeamStrengthModel` — attack/defence strengths vs league average.
8. `PoissonPredictionModel` — compute λhome/λaway; score matrix; derive 1X2, O1.5/O2.5/U2.5/U3.5/U4.5,
   BTTS, Double Chance, Draw, Correct Score (top N), from distribution.
9. `FormPredictionModel`, `HomeAwayModel`, `OddsValueModel` (implied probs + vig removal + value).
10. `EnsemblePredictionModel` — weighted blend (Poisson-dominant) of component models, outputs full
    probability vector + expected goals.
11. `ConfidenceEngine` — 0-100 from data completeness, model agreement, form consistency, home/away
    consistency, market agreement, odds availability, league data quality, stability (NOT probability).
12. `MarketPredictionModel` — builds all market outputs (1X2, O/U, BTTS, DC, draw, correct score) from
    blended probabilities with per-category min_confidence (stricter for correct score).
13. Refactor `App\Services\PredictionEngine::predictFixture()` to delegate to Ensemble + Confidence +
    Market models so existing callers (HomeController, MatchDetailController, GeneratePredictions)
    keep working while internals become modular. Keep output shape backward-compatible.

### Phase 1C — Admin league + category control
14. Register `admin` alias in `bootstrap/app.php` (`$middleware->alias(['admin'=>AdminMiddleware::class])`)
    and attach `->middleware(['auth','admin'])` to the admin route group in routes/web.php.
15. Define Gates for prediction.view/override/publish/settings/league.manage/performance.view
    (currently resolve to is_admin; extensible later) in a provider.
16. `Admin\PredictionLeagueController` + view `admin/prediction-leagues` — list/enable/disable, season,
    min_confidence, prediction_enabled, homepage_enabled, priority, auto_publish; show upcoming count,
    generated count, recent accuracy, last sync time. Toggle routes.
17. `Admin\PredictionCategoryController` + view `admin/prediction-settings` — enable/disable markets,
    min_confidence, homepage_enabled, sort_order; plus global engine settings (auto publish,
    min confidence/probability, lookahead, AI on/off/provider).

### Phase 1D — Data pipeline + generation jobs
18. Jobs (app/Jobs/Prediction/): SyncEnabledLeaguesJob, SyncFixturesJob, SyncStandingsJob,
    SyncTeamStatisticsJob, SyncH2HJob, SyncOddsJob, GeneratePredictionsJob, PublishPredictionsJob,
    LockPredictionsJob, UpdatePredictionResultsJob, CalculatePredictionPerformanceJob.
19. Commands (app/Console/Commands/Prediction/): predictions:sync-leagues, predictions:sync-fixtures,
    predictions:sync-stats, predictions:publish, predictions:lock, predictions:performance.
    Refactor `predictions:generate` to: read enabled leagues (not hardcoded array), dispatch
    GeneratePredictionsJob per fixture, persist per-market rows (1 row per market_code per
    fixture+model_version), log via prediction_logs (fixture_id, model_version, inputs, probabilities,
    confidence, selection, publish status, errors, execution time). Reuse ApiFootballServiceEnhanced
    (no duplicate client). No unnecessary calls — cache 30 min.
20. Update `routes/console.php` schedule: sync fixtures/standings hourly; generate predictions hourly;
    publish near kickoff; lock 30 min before kickoff; resolve results every 5 min after matches;
    performance daily.

### Phase 1E — Result processing + performance
21. `PredictionResultService` — resolve per-market WON/LOST/VOID from final score (all markets incl.
    correct score, BTTS, O/U, DC, draw, 1X2). Audit trail in prediction_logs. Update statuses
    (won/lost/void/no_bet). Refactor `UpdateLiveScores::evaluatePredictions()` to use it.
22. `PredictionPerformanceService` — total/won/lost/pending/void, accuracy, win rate, ROI, yield,
    avg confidence, by league/market/confidence-range/model-version, over 7/30/90 days/season/all-time.
    Persist snapshots to prediction_performance.
23. `Admin\PredictionPerformanceController` + view `admin/predictions/performance` — dashboard incl.
    per-market and per-league accuracy tables + calibration note.

### Phase 1F — Public league + match pages + SEO
24. Register `api: routes/api.php` in bootstrap/app.php; create `routes/api.php` + `Api\PredictionApiController`
    (GET /api/predictions, /api/predictions/{id}, /today, /{league}, /{league}/{fixture}, /sure-picks,
    /featured). No private config exposed.
25. `PredictionRepository` — published predictions query layer: surePicks (1X2 only), featured
    (1X2 + Double Chance), byLeague, byCategory, upcoming filters (today/tomorrow/7d/all). Cache keys
    sure_picks / featured_predictions / league_predictions:{slug}; invalidate on publish/edit/remove/expire.
26. Public routes: `/predictions/{league:slug}` -> `PredictionController@league`; `/predictions/{league}/{fixture:slug}`
    -> `PredictionController@matchPrediction` (or dedicated controller). Dynamic, no per-league duplication.
27. Views `predictions/league.blade.php` (logo, name, upcoming fixtures, categories, date/kickoff, teams,
    prediction, probability, confidence, odds, status; filters today/tomorrow/7d/all + market filter) and
    `predictions/match.blade.php` (match, form, home/away stats, H2H, odds, statistical prediction,
    probability, confidence, recommended market, correct-score probabilities, explanation, historical
    model performance). SEO: dynamic title/meta/canonical/OG + Schema.org JSON-LD; no keyword stuffing.

### Phase 1G — Admin prediction editor + overrides + locking
28. `Admin\PredictionAdminController` + `admin/predictions` list + `admin/predictions/{prediction}/edit`
    editor: show AI model selection + probability + confidence + model version + status; admin can
    override selection (Home/Draw/Away/Over 1.5/Over 2.5/Double Chance/BTTS/Correct Score/No Bet) with
    reason; preserve original via original_selection + prediction_overrides row. Feature/unfeature
    (featured, featured_priority, featured_until, admin_featured). Publish/unpublish.
29. Locking: LockPredictionsJob sets LOCKED 30 min before kickoff; editing disabled unless override
    permission. Override endpoint guarded by prediction.override gate.
30. Wire admin nav links in layouts/app.blade.php (admin section).

### Phase 1H — Frontend components + homepage wiring
31. Blade components under resources/views/components/predictions/: prediction-card, confidence-badge,
    probability, league-prediction-list, sure-pick-card, featured-selection-card, correct-score-card,
    prediction-filter, match-prediction-panel, prediction-statistics. Keep existing visual identity.
32. Refactor `home.blade.php` Sure Picks (1X2 only) + Most Featured (1X2 + Double Chance) sections to
    render from repository data (no inline engine calls). Refactor `HomeController@index` to use
    PredictionRepository + cache instead of live predictFixture().
33. Confidence language helper: Very High/High/Moderate/Low; never "guaranteed/100%/fixed". Correct-score
    never labeled "sure".

### Phase 1I — Tests
34. Create tests/ scaffolding (Unit + Feature). Cover: Poisson/O1.5/O2.5/BTTS/DC/draw/1X2/correct-score
    math, confidence calc, no-bet threshold, league enable/disable, category enable/disable,
    fixture sync, duplicate prevention (unique constraint), admin override + audit, locking, result
    resolution per market, sure-picks (1X2 only), featured selection, league pages, API failure fallback,
    AI failure fallback. Run `php artisan test`.

## Phase 2 — Optional AI explanation layer
35. `app/Services/AI/AIProviderInterface` + `NullAIProvider` + `ClaudeAIProvider` + `OpenAIProvider` +
    factory binding by AI_PROVIDER. AI only writes match summary/reasons/risk/explanation; never invents
    stats, never changes probability, never claims certainty. `GeneratePredictionExplanationJob` runs when
    AI_ENABLED. When null/absent, `PredictionExplanationService` emits statistical template text
    (model still fully functional without Claude).

## Phase 3 — Backtesting + ML interface
36. `MachineLearningPredictionModel` implementing PredictionModelInterface (placeholder for XGBoost/LightGBM/etc.).
37. Backtest: persist team_statistics + head_to_head snapshots; `predictions:backtest` command + service that
    simulate predictions per (league, season, date range, market, model version) using only data available
    before each match date (no leakage). Show predictions/wins/losses/accuracy/ROI/yield/drawdown/streaks/calibration.
38. ML training on Esurebet historical data later.

## Relevant files (key)
- Modify: bootstrap/app.php, routes/web.php, routes/console.php, config/services.php, .env.example,
  app/Models/Fixture.php, app/Models/Prediction.php, app/Services/PredictionEngine.php,
  app/Console/Commands/GeneratePredictions.php, app/Console/Commands/UpdateLiveScores.php,
  app/Services/TipCategorizer.php, app/Http/Controllers/HomeController.php,
  app/Http/Controllers/PredictionController.php, resources/views/home.blade.php,
  resources/views/layouts/app.blade.php.
- New: config/prediction.php; routes/api.php; migrations above; models above; services under
  app/Services/Prediction/ + app/Services/AI/; jobs under app/Jobs/Prediction/; commands under
  app/Console/Commands/Prediction/; controllers under app/Http/Controllers/Admin/ + Api/; views under
  resources/views/admin/ + resources/views/predictions/ + resources/views/components/predictions/;
  seeders; tests/Unit + tests/Feature.

## Verification
1. `php artisan migrate:fresh --seed` (or `migrate` on existing DB + seeders) succeeds; leagues/categories/models seeded.
2. `php artisan predictions:sync-leagues` then `predictions:sync-fixtures --days=3` populates fixtures from enabled leagues only.
3. `php artisan predictions:generate` creates per-market rows with unique (fixture_id, market_code, model_version); no duplicates on re-run.
4. Unit tests pass: `php artisan test` (Poisson/markets/confidence/no-bet/result/override).
5. Manual: disable a league -> no new predictions, homepage/league page omit it, historical rows remain.
6. Manual: set min_confidence high -> low-confidence rows become NO_BET and are not published.
7. Manual: admin override a selection with reason -> original preserved in prediction_overrides, new selection shown, audit logged.
8. Manual: lock predictions 30 min pre-kickoff -> editor disabled for non-override users.
9. Manual: after FT, `scores:update-live` resolves each market to WON/LOST/VOID correctly (incl. correct score, BTTS, O/U, DC, draw).
10. Manual: performance dashboard shows accuracy/ROI by league/market/model/confidence range over 7/30/90 days.
11. Manual: homepage Sure Picks shows only 1X2; Featured shows 1X2 + Double Chance; pages load fast (no live API calls).
12. Manual: league pages (/predictions/premier-league etc.) + match page render with SEO meta; filters work.
13. Manual: API-Football outage -> engine does not fabricate; missing stats reduce confidence; odds absence doesn't fail engine.
14. Manual: AI_PROVIDER=null -> statistical explanations still emitted; system fully functional without Claude.

## Decisions
- Six league IDs: PL=39, LaLiga=140, SerieA=135, Bundesliga=78, Ligue1=61, Eredivisie=88; season default 2025, per-league configurable.
- Normalized per-market rows in `predictions`; legacy single-row/columns retained for backward compat (views + existing homepage continue to work).
- Reuse ApiFootballServiceEnhanced as the single football client; reuse prediction_logs/teams/betting_odds tables; no duplicate clients/tables.
- Admin secured via auth+admin middleware + Gates (prediction.*) resolving to is_admin for now.
- Database queue + file cache retained; Redis stays optional.
- No accuracy claims; confidence/probability honest; NO_BET supported; correct score stricter thresholds.

## Further Considerations
1. Legacy `*_tip_content` columns and `TipCategorizer` boolean-flag model may be deprecated after public pages migrate fully to normalized rows; keep both working during transition.
2. `Fixture::upcoming()` scope and `fixtures.elapsed` column are referenced but undefined/missing — Phase 1A adds them.
3. `app/Http copy/` is an orphaned duplicate (not autoloaded) — recommend deleting in a cleanup step (optional, out of core scope).
