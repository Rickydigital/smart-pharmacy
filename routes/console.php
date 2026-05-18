<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventory:generate-alerts')
    ->dailyAt('07:00')
    ->withoutOverlapping();

Schedule::command('system:detect-pending-actions')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('smart-control:sync-branches')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('smart-control:sync')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('smart-control:heartbeat')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('smart-control:sync-market-products')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('intelligence:generate')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('backup:run --only-db')
    ->dailyAt('01:00')
    ->withoutOverlapping();

Schedule::command('backup:run')
    ->sundays()
    ->at('02:00')
    ->withoutOverlapping();

Schedule::command('backup:clean')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('backup:monitor')
    ->dailyAt('04:00')
    ->withoutOverlapping();
