<?php

namespace App\Console\Commands\Fire;

use Throwable;
use Illuminate\Console\Command;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Jobs\Fire\AutoUnpublishUnconfirmedFiresJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;

class SendFiresCheckinsEmailCommand extends Command
{
    protected $signature = 'fire:send-checkins';

    protected $description = 'Send check-in emails, reminders, and auto-unpublish unconfirmed fires.';

    public function handle(): int
    {
        try {
            $this->info('Dispatching fire check-in jobs...');

            SendFireCheckinsEmailJob::dispatch();
            $this->line('  - Initial check-in emails job dispatched');

            SendFireCheckinsReminderEmailJob::dispatch();
            $this->line('  - Reminder emails job dispatched');

            AutoUnpublishUnconfirmedFiresJob::dispatch();
            $this->line('  - Auto-unpublish job dispatched');

            $this->info('All jobs dispatched successfully.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Failed to dispatch jobs: {$e->getMessage()}");
            report($e);

            return self::FAILURE;
        }
    }
}
