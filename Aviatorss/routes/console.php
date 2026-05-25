<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Optional: run daily sync if the scheduler is configured (cron / schedule:work).
Schedule::command('schedule:parse-groups')->monthlyOn(1, '03:00');
