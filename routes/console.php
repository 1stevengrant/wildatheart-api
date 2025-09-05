<?php

use App\Enums\ReportName;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Admin\GenerateAndEmailReport;
use App\Jobs\Transistor\RefreshDailyReadingsJob;

Schedule::command(\App\Console\Commands\Fire\SendFiresCheckinsEmailCommand::class)->daily();
Schedule::command('app:get-latest-subsplash-media')->everyFifteenMinutes();
Schedule::command('wegive:update-donor-ids')->daily();
// Generate the monthly BAKR report and send to Morgan.
Schedule::job(new GenerateAndEmailReport(
    'morgan@becomegoodsoil.com',
    ReportName::BAKR_HOSTS,
    'Becoming A King Retreat Hosts'
))->monthly();

Schedule::job(new RefreshDailyReadingsJob)->everyThirtyMinutes();
