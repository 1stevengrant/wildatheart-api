<?php

namespace App\Console\Commands\Event;

use App\Models\Event;
use Illuminate\Console\Command;

class UpdateEventTypeGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-event-type-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::query()->withTrashed()->get();
        $events->each(function ($event) {
            $event->update([]);
        });

        $this->info('Event type groups updated successfully.');
    }
}
