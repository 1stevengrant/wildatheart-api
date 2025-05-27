<?php

use App\Enums\ReportName;
use App\Jobs\GenerateAndEmailReport;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-monthly-email-to-fire-hosts')->daily();
Schedule::command('app:unpublish-inactive-fires')->daily();
Schedule::command('app:send-seven-day-followup')->daily();
Schedule::command('app:get-latest-subsplash-media')->hourly();
Schedule::command('wegive:update-donor-ids')->daily();
// Generate the monthly BAKR report and send to Morgan.
Schedule::job(new GenerateAndEmailReport(
    'morgan@becomegoodsoil.com',
    ReportName::BAKR_HOSTS,
    'Becoming A King Retreat Hosts'
))->monthly();
