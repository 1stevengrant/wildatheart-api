<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use App\Jobs\Fire\ProcessFireScheduleJob;

class ProcessFireSchedulesCommand extends Command
{
    protected $signature = 'fire:process-schedules';

    protected $description = 'Renew Weekly and Monthly Fire schedules, remind Custom ones, and unpublish lapsed Custom Fires';

    public function handle(): int
    {
        $count = 0;

        Event::query()
            ->allFires()
            ->published()
            ->withSchedule()
            ->chunkById(100, function ($events) use (&$count) {
                foreach ($events as $event) {
                    $this->line("Queueing schedule check for Fire `{$event->id}`...");
                    ProcessFireScheduleJob::dispatch($event);
                    $count++;
                }
            });

        $this->comment("Queued {$count} scheduled Fires.");

        return self::SUCCESS;
    }
}
