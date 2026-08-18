<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Automated Daily Database Backup (02:00 AM UTC) ───────────────────────────
// Generates compressed PostgreSQL dump, syncs to S3 cloud bucket, and prunes older files.
\Illuminate\Support\Facades\Schedule::command('db:backup --cloud --prune=30')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup.log'));

