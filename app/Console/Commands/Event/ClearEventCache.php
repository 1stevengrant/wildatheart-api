<?php

namespace App\Console\Commands\Event;

use Illuminate\Console\Command;
use App\Services\EventCacheService;

class ClearEventCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-event-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    /**
     * Called by EventObserver on created, updated, deleted, and restored events.
     */
    protected $description = 'Clears the API event cache';

    public function handle(EventCacheService $eventCacheService): void
    {
        $eventCacheService->flush();

        $this->info('Event cache cleared!');
    }
}
