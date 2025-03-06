<?php

namespace App\Console\Commands\Event;

use App\Models\Event;
use Illuminate\Console\Command;

class CSVEventExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:csv-event-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $events = Event::query()
            ->withTrashed()
            ->where('country', '=', 'US')
            ->orWhere('country', '=', 'United States')
            ->get();

        $csv = fopen('events.csv', 'w');

        fputcsv($csv, ['Type', 'Status', 'Created Date', 'Event Start Date', 'Name', 'Address', 'City', 'State', 'Zip', 'Phone', 'Email']);

        foreach ($events as $event) {
            fputcsv($csv, [
                $event->eventType->name,
                $event->approved_at ? 'Active' : 'Inactive',
                $event->created_at,
                $event->start_date,
                $event->user->fullname,
                $event->address?->street_address,
                $event->address?->city,
                $event->address?->state,
                $event->address?->zip,
                $event->address?->phone,
                $event->user->email,
            ]);
        }

        fclose($csv);
    }
}
