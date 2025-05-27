<?php

namespace App\Console\Commands\Fire;

use Illuminate\Console\Command;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Jobs\Fire\AutoUnpublishUnconfirmedFiresJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;

class SendFiresCheckinsEmailCommand extends Command
{
    protected $signature = 'app:send-fires-checkins-email-command';

    protected $description = 'This command sends check-in emails to event hosts and handles auto-unpublishing of events.';

    public function handle(): void
    {
        SendFireCheckinsEmailJob::dispatch();
        SendFireCheckinsReminderEmailJob::dispatch();
        AutoUnpublishUnconfirmedFiresJob::dispatch();
    }
}
