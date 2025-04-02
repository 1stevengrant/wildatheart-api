<?php

use App\Enums\ReportName;
use App\Jobs\GenerateAndEmailReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:send-monthly-email-to-fire-hosts')->daily();
Schedule::command('app:unpublish-inactive-fires')->daily();
Schedule::command('app:send-seven-day-followup')->daily();
Schedule::command('app:get-latest-subsplash-media')->hourly();

// Generate the monthly BAKR report and send to Morgan.
Schedule::job(new GenerateAndEmailReport(
    'morgan@becomegoodsoil.com',
    ReportName::BAKR_HOSTS,
    'Becoming A King Retreat Hosts'
))->monthly();
