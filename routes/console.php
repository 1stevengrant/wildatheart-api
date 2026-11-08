<?php

use App\Models\EmailLog;
use App\Enums\ReportName;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Admin\GenerateAndEmailReport;
use App\Jobs\Transistor\RefreshDailyReadingsJob;
use App\Console\Commands\GetLatestSubsplashMedia;
use App\Console\Commands\ArchiveActivityLogCommand;
use App\Console\Commands\SyncStagingContentCommand;
use App\Console\Commands\WeGive\UpdateDonorIdCommand;
use App\Console\Commands\CleanupExpiredContactInvitations;
use App\Console\Commands\Fire\ProcessFireSchedulesCommand;
use App\Console\Commands\Fire\SendFiresCheckinsEmailCommand;

Schedule::command(SendFiresCheckinsEmailCommand::class)->daily()->environments('production');
// 15:00 UTC is morning across the US, where most Fires are. Production only, so
// staging never emails hosts about schedules created from previews.
Schedule::command(ProcessFireSchedulesCommand::class)->dailyAt('15:00')->onOneServer()->withoutOverlapping()->environments('production');
Schedule::command(GetLatestSubsplashMedia::class)->everyFifteenMinutes()->environments('production');
Schedule::command(UpdateDonorIdCommand::class)->daily()->environments('production');
Schedule::command(CleanupExpiredContactInvitations::class)->hourly()->withoutOverlapping();
Schedule::command('model:prune', ['--model' => [EmailLog::class]])->daily();
Schedule::command(ArchiveActivityLogCommand::class)->monthly()->onOneServer()->withoutOverlapping()->runInBackground()->environments('production');
// Generate the monthly BAKR report and send to Morgan.
Schedule::job(new GenerateAndEmailReport(
    [
        'morgan@becomegoodsoil.com',
        'andrew@becomegoodsoil.com',
    ],
    ReportName::BAKR_HOSTS,
    'Becoming A King Retreat Hosts'
))->monthly()->environments('production');

Schedule::job(new RefreshDailyReadingsJob)->everyThirtyMinutes()->environments('production');
Schedule::command(SyncStagingContentCommand::class)->hourly()->onOneServer()->withoutOverlapping()->environments('staging');
