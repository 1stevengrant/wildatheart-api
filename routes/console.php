<?php

use App\Enums\ReportName;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Admin\GenerateAndEmailReport;
use App\Jobs\Transistor\RefreshDailyReadingsJob;
use App\Console\Commands\GetLatestSubsplashMedia;
use App\Console\Commands\WeGive\UpdateDonorIdCommand;
use App\Console\Commands\CleanupExpiredContactInvitations;
use App\Console\Commands\Fire\SendFiresCheckinsEmailCommand;

Schedule::command(SendFiresCheckinsEmailCommand::class)->daily();
Schedule::command(GetLatestSubsplashMedia::class)->everyFifteenMinutes()->environments('production');
Schedule::command(UpdateDonorIdCommand::class)->daily();
Schedule::command(CleanupExpiredContactInvitations::class)->hourly()->withoutOverlapping();
// Generate the monthly BAKR report and send to Morgan.
Schedule::job(new GenerateAndEmailReport(
    [
        'morgan@becomegoodsoil.com',
        'andrew@becomegoodsoil.com',
    ],
    ReportName::BAKR_HOSTS,
    'Becoming A King Retreat Hosts'
))->monthly();

Schedule::job(new RefreshDailyReadingsJob)->everyThirtyMinutes()->environments('production');
