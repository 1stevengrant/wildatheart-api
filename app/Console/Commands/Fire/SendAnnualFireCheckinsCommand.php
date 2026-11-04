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

        $count = 0;
        $limitInt = $limit ? (int) $limit : null;

        if ($isDryRun) {
            if ($limitInt) {
                $query->limit($limitInt);
            }
            $events = $limitInt ? $query->get() : $query->cursor();

            foreach ($events as $event) {
                $count++;
                $this->line("  - [{$event->id}] {$event->name} (Host: {$event->user?->email})");
            }

            $this->info("Found {$count} eligible fire(s).");

            return self::SUCCESS;
        }

        $query->chunkById(100, function ($events) use (&$count, $limitInt) {
            foreach ($events as $event) {
                if ($limitInt !== null && $count >= $limitInt) {
                    return false; // Stop chunking
                }
                AnnualFireCheckinEmailJob::dispatch($event);
                $count++;
            }

            // Return false to stop chunking if limit reached
            return $limitInt === null || $count < $limitInt;
        });

        $this->info("Queued {$count} annual check-in emails. The queue will process them.");

        return self::SUCCESS;
    }
}
