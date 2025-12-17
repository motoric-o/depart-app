<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up old backups daily at 01:00
Schedule::command('backup:clean')->dailyAt('01:00');

// Run new backup daily at 01:30
Schedule::command('backup:run')->dailyAt('01:30');

Schedule::call(function () {
    
    // Rule 1: Force expire (delete) anything older than 60 days
    // regardless of whether it was read or not.
    DB::table('notifications')
        ->where('created_at', '<', now()->subDays(60))
        ->delete();

    // Rule 2: Delete notifications that were READ more than 30 days ago.
    DB::table('notifications')
        ->whereNotNull('read_at') // Must be read
        ->where('read_at', '<', now()->subDays(30)) // Read over 30 days ago
        ->delete();

})->daily();