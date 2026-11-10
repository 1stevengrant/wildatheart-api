<?php

namespace App\Actions\Internal;

use App\Models\Event;
use App\Models\EventScheduleDate;
use Illuminate\Support\Facades\DB;
use App\Services\EventCacheService;
use App\Enums\Event\ScheduleTestPreset;
use App\Data\EventScheduleDateInputData;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Support\Schedule\ScheduleDateWriter;

/**
 * Staging only. Moves every date of a Fire's schedule, and its anchor, by the whole
 * number of weeks that brings the last date closest to the preset. Per-date times are
 * kept. A Monthly Fire can change position in the month (the 2nd Tuesday becomes the
 * 3rd), because months aren't a whole number of weeks.
 */
class MoveFireScheduleDates
{
    use AsAction;

    /**
     * Returns how many days the dates moved, or 0 if they were already in place.
     */
    public function handle(Event $event, ScheduleTestPreset $preset): int
    {
        $schedule = $event->schedule;
        $dates = $event->scheduleDates()->get();

        if (! $schedule || $dates->isEmpty()) {
            return 0;
        }

        $daysUntilLastDate = (int) $schedule->localToday()->diffInDays($dates->max('date'), false);
        $offset = 7 * (int) round(($preset->targetDays() - $daysUntilLastDate) / 7);

        if ($offset === 0) {
            return 0;
        }

        DB::transaction(function () use ($event, $schedule, $dates, $offset) {
            EventScheduleDate::where('event_id', $event->id)->delete();

            ScheduleDateWriter::write($schedule, $dates->map(fn (EventScheduleDate $date) => new EventScheduleDateInputData(
                $date->date->addDays($offset)->toDateString(),
                $date->start_time ? mb_substr($date->start_time, 0, 5) : null,
                $date->end_time ? mb_substr($date->end_time, 0, 5) : null,
            )));

            if ($schedule->anchor_date) {
                $schedule->update(['anchor_date' => $schedule->anchor_date->addDays($offset)]);
            }

            app(EventCacheService::class)->flush();
        });

        return $offset;
    }
}
