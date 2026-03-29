<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fees:charge-monthly')
    ->monthlyOn(1, '00:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('interest:apply-monthly')
    ->monthlyOn(1, '00:00')
    ->withoutOverlapping()
    ->runInBackground();
