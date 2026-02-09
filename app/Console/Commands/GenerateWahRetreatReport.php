<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateWahRetreatReport extends Command
{
    protected $signature = 'report:wah-retreat';

    protected $description = 'Generate CSV report of Wild at Heart Retreat events with attendee counts';

    public function handle(): int
    {
        $this->info('Generating Wild at Heart Retreat report...');

        // Get the Wild at Heart Retreat event type
        $wahRetreatType = EventType::where('name', EventType::WAH_RETREAT)->first();

        if (! $wahRetreatType) {
            $this->error('Wild at Heart Retreat event type not found!');

            return self::FAILURE;
        }

        // Query all Wild at Heart Retreat events (including trashed) with attendee counts
        $events = Event::withTrashed()
            ->with('user')
            ->where('event_type_id', $wahRetreatType->id)
            ->withCount('attendees')
            ->orderBy('name')
            ->get();

        if ($events->isEmpty()) {
            $this->warn('No Wild at Heart Retreat events found.');

            return self::SUCCESS;
        }

        // Generate CSV content
        $csvData = collect([['Event Name', 'Attendee Count', 'Status']]);

        $events->each(function (Event $event) use ($csvData) {
            $eventName = $event->name;

            // Use host name as fallback if event name is empty
            if (empty($eventName) && $event->user) {
                $eventName = ucwords($event->user->fullname) . ' (Host)';
            }

            // Determine event status
            $status = match (true) {
                $event->trashed() => 'Deleted',
                $event->approved_at !== null => 'Published',
                default => 'Unpublished',
            };

            $csvData->push([
                $eventName,
                $event->attendees_count,
                $status,
            ]);
        });

        // Create reports directory if it doesn't exist
        if (! Storage::exists('reports')) {
            Storage::makeDirectory('reports');
        }

        // Generate filename with timestamp
        $filename = 'reports/wah-retreat-report-' . now()->format('Y-m-d-His') . '.csv';

        // Write CSV file
        $csv = $csvData->map(fn ($row) => implode(',', array_map(fn ($field) => '"' . str_replace('"', '""', $field) . '"', $row)))
            ->implode("\n");

        Storage::put($filename, $csv);

        $fullPath = Storage::path($filename);

        $this->info('Report generated successfully!');
        $this->line("Total events: {$events->count()}");
        $this->line("Total attendees: {$events->sum('attendees_count')}");
        $this->newLine();
        $this->comment("File saved to: $fullPath");

        return self::SUCCESS;
    }
}
