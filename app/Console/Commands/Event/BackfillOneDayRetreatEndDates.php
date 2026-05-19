<?php

namespace App\Console\Commands\Event;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillOneDayRetreatEndDates extends Command
{
    protected $signature = 'events:backfill-one-day-retreat-end-dates {--dry-run : Show affected events without writing}';

    protected $description = 'Force end_date to equal start_date on all 1-Day Captivating Retreat events';

    public function handle(): int
    {
        $eventTypeId = EventType::query()
            ->where('name', EventType::CAPTIVATING_CORE_RETREAT_1_DAY)
            ->value('id');

        if (! $eventTypeId) {
            $this->error('Event type "1-Day Captivating Retreat" not found.');

            return self::FAILURE;
        }

        $query = Event::query()
            ->where('event_type_id', $eventTypeId)
            ->whereColumn('end_date', '!=', 'start_date');

        $affected = $query->count();

        if ($affected === 0) {
            $this->info('No 1-Day Captivating Retreat events need updating.');

            return self::SUCCESS;
        }

        $this->info("Found {$affected} 1-Day Captivating Retreat event(s) with mismatched end_date.");

        $query->get(['id', 'name', 'start_date', 'end_date'])
            ->each(fn (Event $event) => $this->line(
                "{$event->id} | {$event->name} | {$event->start_date->toDateString()} -> {$event->end_date->toDateString()}"
            ));

        if ($this->option('dry-run')) {
            $this->comment('Dry run: no changes written.');

            return self::SUCCESS;
        }

        Event::withoutEvents(function () use ($eventTypeId) {
            Event::query()
                ->where('event_type_id', $eventTypeId)
                ->whereColumn('end_date', '!=', 'start_date')
                ->update(['end_date' => DB::raw('start_date')]);
        });

        cache()->forget('events');

        $this->info("Updated {$affected} event(s). Event cache cleared.");

        return self::SUCCESS;
    }
}
