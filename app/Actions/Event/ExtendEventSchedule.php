<?php

namespace App\Actions\Event;

use Carbon\CarbonImmutable;
use App\Models\EventSchedule;
use Illuminate\Support\Collection;
use App\Data\EventScheduleDateInputData;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Support\Schedule\ScheduleDateWriter;
use App\Support\Schedule\ScheduleDateGenerator;

/**
 * Appends the next four Weekly or Monthly dates after the later of the last stored
 * date and yesterday, so a Fire that missed renewals restarts from today rather than
 * filling in the past. Dates follow the anchor's pattern, so a moved date does not
 * shift the series.
 */
class ExtendEventSchedule
{
    use AsAction;

    /**
     * @return Collection<int, CarbonImmutable>
     */
    public function handle(EventSchedule $schedule): Collection
    {
        $dates = $this->nextDates($schedule);

        ScheduleDateWriter::write(
            $schedule,
            $dates->map(fn (CarbonImmutable $date) => new EventScheduleDateInputData($date->toDateString())),
        );

        return $dates;
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    public function nextDates(EventSchedule $schedule): Collection
    {
        if (! $schedule->autoRenews() || ! $schedule->anchor_date) {
            return collect();
        }

        $yesterday = $schedule->localToday()->subDay();
        $lastDate = $schedule->dates()->max('date');
        $after = $lastDate && CarbonImmutable::parse($lastDate)->greaterThan($yesterday)
            ? CarbonImmutable::parse($lastDate)
            : $yesterday;

        return ScheduleDateGenerator::datesAfter($schedule->frequency, $schedule->anchor_date, $after);
    }
}
