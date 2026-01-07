<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use App\Jobs\Fire\AnnualFireCheckinEmailJob;

class SendAnnualFireCheckinsCommand extends Command
{
    protected $signature = 'fire:annual-checkin
                            {--dry-run : Preview eligible fires without sending emails}
                            {--limit= : Limit the number of fires to process}';

    protected $description = 'Send annual check-in emails to Fire & Captivating Fire hosts not verified in 30+ days.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        $this->info($isDryRun ? 'Dry run - previewing eligible fires...' : 'Sending annual check-in emails...');

        $thirtyDaysAgo = now()->subDays(30);

        $query = Event::query()
            ->with(['user', 'managers'])
            ->allFires()
            ->published()
            ->where(function ($query) use ($thirtyDaysAgo) {
                $query->whereNull('last_checkin_confirmed_at')
                    ->orWhere('last_checkin_confirmed_at', '<=', $thirtyDaysAgo);
            })
            ->where('created_at', '<=', $thirtyDaysAgo)
            ->where(function ($query) use ($thirtyDaysAgo) {
                $query->whereNull('last_checkin_email_sent_at')
                    ->orWhere('last_checkin_email_sent_at', '<=', $thirtyDaysAgo);
            });

        if ($limit) {
            $query->limit((int) $limit);
        }

        $count = 0;

        if ($isDryRun) {
            $events = $limit ? $query->get() : $query->cursor();

            foreach ($events as $event) {
                $count++;
                $this->line("  - [{$event->id}] {$event->name} (Host: {$event->user?->email})");
            }

            $this->info("Found {$count} eligible fire(s).");

            return self::SUCCESS;
        }

        $query->chunkById(100, function ($events) use (&$count) {
            foreach ($events as $event) {
                AnnualFireCheckinEmailJob::dispatch($event);
                $count++;
            }
        });

        $this->info("Queued {$count} annual check-in emails. Horizon will process them.");

        return self::SUCCESS;
    }
}
