<?php

use Illuminate\Support\Facades\Schedule;

// Generate predictions daily at 6 AM
Schedule::command('predictions:generate --days=3')->dailyAt('06:00');

// Update live scores every 5 minutes during match hours
Schedule::command('scores:update-live')->everyFiveMinutes()->between('12:00', '23:59');

// Generate basketball predictions daily at 7 AM
Schedule::command('predictions:basketball')->dailyAt('07:00');

// Deactivate expired user subscriptions every hour
Schedule::command('users:deactivate-expired')->hourly();
