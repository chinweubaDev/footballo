<?php

use Illuminate\Support\Facades\Schedule;

// Generate predictions at midnight (new day) and 6 AM (backup)
Schedule::command('predictions:generate --days=3')->dailyAt('00:05');
Schedule::command('predictions:generate --days=3')->dailyAt('06:00');

// Update live scores every 5 minutes during match hours
Schedule::command('scores:update-live')->everyFiveMinutes()->between('12:00', '23:59');

// Resolve predictions for finished matches (idempotent) and refresh caches.
// Runs after matches typically finish and again overnight for catch-up.
Schedule::command('predictions:resolve-results')->everyFiveMinutes()->between('18:00', '23:59');
Schedule::command('predictions:resolve-results')->dailyAt('02:30');

// Generate basketball predictions daily at 7 AM
Schedule::command('predictions:basketball')->dailyAt('07:00');

// Deactivate expired user subscriptions every hour
Schedule::command('users:deactivate-expired')->hourly();

// ─── Telegram Automation ───

// Post the surest tip of the day at 10:00 AM (after predictions are generated)
Schedule::command('telegram:post-surest-tip')->dailyAt('10:00');

// Post yesterday's match result at 9:00 AM
Schedule::command('telegram:post-match-result')->dailyAt('09:00');

// Post VIP/VVIP promotion every 2 days at 12:00 PM
Schedule::command('telegram:post-promotion')->cron('0 12 */2 * *');

// Auto-generate blog posts twice daily (8 AM & 8 PM)
Schedule::command('blog:auto-generate')->twiceDaily(8, 20);
