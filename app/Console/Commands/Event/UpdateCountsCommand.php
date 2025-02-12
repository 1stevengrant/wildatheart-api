<?php

namespace App\Console\Commands\Event;

use App\Models\Attendee;
use Illuminate\Console\Command;

class UpdateCountsCommand extends Command
{
    protected $signature = 'app:update-counts-command';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info('Update counts command');
        $attendees = Attendee::all();

        foreach ($attendees as $attendee) {
            if ($attendee->event) {
                $this->info('Updating counts for event: '.$attendee->event->name ?? $attendee->event);
                $attendee->event->increment('attendee_count');
                $this->info('Attendee count is now: '.$attendee->event->attendee_count);
            }

        }

        $this->info('Counts updated');

        return 0;

    }
}
