<?php

use App\Jobs\Pipeline\AggregatePerformanceJob;
use App\Jobs\Pipeline\GeneratePredictionsJob;
use App\Jobs\Pipeline\GenerateShadowPredictionsJob;
use App\Jobs\Pipeline\LockPredictionsJob;
use App\Jobs\Pipeline\ResolveResultsJob;
use App\Jobs\Pipeline\SyncFixturesJob;
use App\Jobs\Pipeline\UpdateLiveScoresJob;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Phase 1L — queued pipeline schedule.
|--------------------------------------------------------------------------
| The scheduler dispatches QUEUED JOBS (not synchronous commands) so the
| pipeline is retryable, observable and non-blocking. Job priorities are
| assigned per job class (§4): high = lock/live-scores/resolve,
| normal = sync/generate/shadow, low = aggregate.
*/

// Fixture synchronization — frequent, so predictions always have fixtures.
Schedule::job(new SyncFixturesJob())->hourly()->between('06:00', '23:59')->withoutOverlapping();

// Prediction generation (v1.0.0) at midnight and 6 AM (backup).
Schedule::job(new GeneratePredictionsJob())->dailyAt('00:05')->withoutOverlapping();
Schedule::job(new GeneratePredictionsJob())->dailyAt('06:00')->withoutOverlapping();

// Shadow generation (v1.1.0) immediately after production generation.
Schedule::job(new GenerateShadowPredictionsJob())->dailyAt('00:06')->withoutOverlapping();
Schedule::job(new GenerateShadowPredictionsJob())->dailyAt('06:01')->withoutOverlapping();

// Lock predictions approaching kickoff (30 min window) — frequent.
Schedule::job(new LockPredictionsJob())->everyTenMinutes()->withoutOverlapping();

// Update live scores every 5 minutes during match hours.
Schedule::job(new UpdateLiveScoresJob())->everyFiveMinutes()->between('12:00', '23:59')->withoutOverlapping();

// Resolve predictions for finished matches (idempotent) and refresh caches.
Schedule::job(new ResolveResultsJob())->everyFiveMinutes()->between('18:00', '23:59')->withoutOverlapping();
Schedule::job(new ResolveResultsJob())->dailyAt('02:30')->withoutOverlapping();

// Performance aggregation — low priority, after settlement.
Schedule::job(new AggregatePerformanceJob())->everyThirtyMinutes()->withoutOverlapping();

// ─── Non-pipeline automation (unchanged) ───

// Generate basketball predictions daily at 7 AM
Schedule::command('predictions:basketball')->dailyAt('07:00');

// Deactivate expired user subscriptions every hour
Schedule::command('users:deactivate-expired')->hourly();

// Post the surest tip of the day at 10:00 AM (after predictions are generated)
Schedule::command('telegram:post-surest-tip')->dailyAt('10:00');

// Post yesterday's match result at 9:00 AM
Schedule::command('telegram:post-match-result')->dailyAt('09:00');

// Post VIP/VVIP promotion every 2 days at 12:00 PM
Schedule::command('telegram:post-promotion')->cron('0 12 */2 * *');

// Auto-generate blog posts twice daily (8 AM & 8 PM)
Schedule::command('blog:auto-generate')->twiceDaily(8, 20);

