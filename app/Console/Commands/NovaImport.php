<?php

namespace App\Console\Commands;

use App\Models\EventType;
use Illuminate\Console\Command;

class NovaImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:nova-import';

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
        $eventTypes = \App\Models\EventType::all();

        $bar = $this->output->createProgressBar($eventTypes->count());

        $eventTypes->each(function (EventType $eventType) use ($bar) {
            if ($eventType->event_schedule) {
                $eventSchedule = [];

                $this->info('Importing '.$eventType->name.' schedule');
                $data = [
                    'id' => bin2hex(random_bytes(4)),
                    'type' => 'text',
                    'enabled' => true,
                    'text_block' => $eventType->event_schedule[0]['fields']['text'],
                ];
                $eventSchedule[] = $data;
                $eventType->event_schedule = $eventSchedule;
                $eventType->save();
            } else {
                $this->info('No schedule for '.$eventType->name);
                $this->info('Skipping...');
            }
            $bar->advance();
        });

        $bar->finish();
    }
}
