<?php

use App\Jobs\EmailLocalReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:send-monthly-email-to-fire-hosts')->daily();
Schedule::command('app:unpublish-inactive-fires')->daily();
Schedule::command('app:send-seven-day-followup')->daily();
// commenting this out until the WAH: Series ids have been updated
// Schedule::command('app:get-latest-subsplash-media')->hourly();

Schedule::job(new EmailLocalReport('morgan@becomegoodsoil.com'))->monthly();
