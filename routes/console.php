<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Automated Daily Database Backup (02:00 AM UTC) ───────────────────────────
// Generates compressed PostgreSQL dump, syncs to S3 cloud bucket, and prunes older files.
Schedule::command('db:backup --cloud --prune=30')
    ->dailyAt('06:00')
    ->timezone('Asia/Phnom_Penh')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup.log'));

// ── Automated Inventory Stockout Risk Analysis (07:00 AM Local) ───────────────
// Analyzes 14-day sales velocity and logs alerts for items below reorder thresholds.
Schedule::command('inventory:check-risks --lookback=14 --threshold=7')
    ->dailyAt('07:00')
    ->timezone('Asia/Phnom_Penh')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/inventory.log'));
