<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class BackfillEventCardHeadings extends Command
{
    protected $signature = 'app:backfill-event-card-headings';

    protected $description = 'Backfill new_card_heading for existing events';

    public function handle(): int
    {
        $this->info('Starting backfill of event card headings...');

        $events = Event::with('eventType')
            ->get();

        if ($events->isEmpty()) {
            $this->info('No events found that need backfilling.');

            return self::SUCCESS;
        }

        $this->info("Found {$events->count()} events to backfill.");

        $progressBar = $this->output->createProgressBar($events->count());
        $progressBar->start();

        $successCount = 0;
        $failedCount = 0;

        foreach ($events as $event) {
            try {
                // Trigger save to invoke the observer
                $event->save();
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("\nFailed to update event ID {$event->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->comment('Backfill complete!');
        $this->info("✓ Successfully updated: {$successCount} events");

        if ($failedCount > 0) {
            $this->error("✗ Failed to update: {$failedCount} events");
        }

        return self::SUCCESS;
    }
}
