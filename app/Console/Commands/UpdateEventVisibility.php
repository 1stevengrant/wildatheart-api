<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class UpdateEventVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-event-visibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the event visibility based on the start date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::all();

        $this->output->progressStart(count($events));

        $events->each(function ($event) {
            $this->output->progressAdvance();
            if ($event->start_date->isPast()) {
                $event->approved_at = null;
                $event->save();
                $this->info('Event '.$event->name.' is in the past');
            }
        });

        $this->output->progressFinish();
    }
}
