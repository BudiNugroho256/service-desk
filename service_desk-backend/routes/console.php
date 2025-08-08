<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('emails:fetch')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/email_fetch.log'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');