<?php

namespace App\Support\Schedule;

use App\Models\EventSchedule;
use App\Models\EventScheduleDate;
use Illuminate\Support\Collection;
use App\Data\EventScheduleDateInputData;

class ScheduleDateWriter
{
    /**
     * @param  Collection<int, EventScheduleDateInputData>  $dates
     */
    public static function write(EventSchedule $schedule, Collection $dates): void
    {
        if ($dates->isEmpty()) {
            return;
        }

        $now = now();

        $rows = $dates->map(function (EventScheduleDateInputData $date) use ($schedule, $now) {
            $startTime = $date->start_time ?: $schedule->defaultStartTime();
            $endTime = $date->end_time ?: $schedule->defaultEndTime();

            return [
                'event_id' => $schedule->event_id,
                'date' => $date->date,
                'start_time' => $date->start_time ?: null,
                'end_time' => $date->start_time ? $date->end_time : null,
                'starts_at' => ScheduleTimes::toUtc($date->date, $startTime, $schedule->timezone),
                'ends_at' => ScheduleTimes::toUtc($date->date, $endTime, $schedule->timezone),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        EventScheduleDate::upsert(
            $rows,
            ['event_id', 'date'],
            ['start_time', 'end_time', 'starts_at', 'ends_at', 'updated_at'],
        );
    }
}
