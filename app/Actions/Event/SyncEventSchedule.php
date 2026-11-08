<?php

namespace App\Actions\Event;

use App\Models\Event;
use Carbon\CarbonImmutable;
use App\Models\EventSchedule;
use App\Models\EventScheduleDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Data\EventScheduleInputData;
use App\Data\EventScheduleDateInputData;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Support\Schedule\ScheduleDateWriter;
use App\Support\Schedule\ScheduleDateGenerator;

/**
 * Saves a Fire's schedule. Submitted dates fully replace the upcoming ones; past dates
 * are never changed. Weekly and Monthly schedules sent without dates get the next four
 * from the anchor date.
 */
class SyncEventSchedule
{
    use AsAction;

    public function handle(Event $event, EventScheduleInputData $input): EventSchedule
    {
        return DB::transaction(function () use ($event, $input) {
            $schedule = EventSchedule::updateOrCreate(
                ['event_id' => $event->id],
                [
                    'frequency' => $input->frequency,
                    'timezone' => $input->timezone,
                    'default_start_time' => $input->default_start_time,
                    'default_end_time' => $input->default_end_time,
                    'anchor_date' => $input->frequency->autoRenews() ? $input->anchor_date : null,
                ],
            );

            $event->forceFill([
                'frequency' => $input->frequency,
                'start_date' => null,
                'end_date' => null,
            ]);

            if ($event->isDirty()) {
                $event->save();
            }

            $today = $schedule->localToday();
            $upcomingDates = $this->upcomingDates($schedule, $input, $today);

            EventScheduleDate::query()
                ->where('event_id', $event->id)
                ->where('date', '>=', $today->toDateString())
                ->whereNotIn('date', $upcomingDates->pluck('date')->all())
                ->delete();

            ScheduleDateWriter::write($schedule, $upcomingDates);

            return $schedule->fresh();
        });
    }

    /**
     * @return Collection<int, EventScheduleDateInputData>
     */
    private function upcomingDates(EventSchedule $schedule, EventScheduleInputData $input, CarbonImmutable $today): Collection
    {
        $dates = collect($input->dates ?? []);

        if ($dates->isEmpty() && $schedule->autoRenews() && $schedule->anchor_date) {
            return ScheduleDateGenerator::datesAfter($schedule->frequency, $schedule->anchor_date, $today->subDay())
                ->map(fn (CarbonImmutable $date) => new EventScheduleDateInputData($date->toDateString()));
        }

        return $dates
            ->map(fn (EventScheduleDateInputData|array $date) => $date instanceof EventScheduleDateInputData
                ? $date
                : EventScheduleDateInputData::from($date))
            ->filter(fn (EventScheduleDateInputData $date) => $date->date >= $today->toDateString())
            ->unique('date')
            ->values();
    }
}
