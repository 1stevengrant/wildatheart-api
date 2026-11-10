<?php

namespace App\Http\Requests\Concerns;

use Throwable;
use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use App\Enums\Event\FrequencyEnum;
use App\Data\EventScheduleInputData;
use Illuminate\Validation\Validator;
use App\Support\Schedule\ScheduleReadiness;

/**
 * Rules for the optional `schedule` body on Fires. Drafts are lenient, but a request
 * that publishes, or edits a Fire that is already listed, must leave it with at least
 * one upcoming date.
 */
trait ValidatesEventSchedule
{
    public const int MAX_UPCOMING_DATES = 24;

    public const int MAX_MONTHS_AHEAD = 12;

    abstract protected function scheduleMustHaveUpcomingDate(): bool;

    protected function isFireEventType(): bool
    {
        $eventType = $this->route('eventType');

        return $eventType instanceof EventType
            && $eventType->group_type === EventType::TYPE_FIRE;
    }

    protected function hasScheduleInput(): bool
    {
        return is_array($this->input('schedule'));
    }

    protected function eventHasSchedule(?Event $event): bool
    {
        return $event instanceof Event && $event->schedule()->exists();
    }

    /**
     * A Fire with a schedule, sent or stored, does not need start_date or frequency.
     */
    protected function presenceRuleForLegacyDateFields(?Event $event): string
    {
        if ($this->isFireEventType() && ($this->hasScheduleInput() || $this->eventHasSchedule($event))) {
            return 'nullable';
        }

        return 'required';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function scheduleRules(): array
    {
        if (! $this->isFireEventType()) {
            return ['schedule' => ['prohibited']];
        }

        $frequencies = array_map(fn (FrequencyEnum $case) => $case->value, FrequencyEnum::scheduleCases());

        return [
            'schedule' => ['nullable', 'array'],
            'schedule.frequency' => ['required_with:schedule', 'string', Rule::in($frequencies)],
            'schedule.timezone' => ['required_with:schedule', 'string', 'timezone:all'],
            'schedule.default_start_time' => ['required_with:schedule', 'date_format:H:i'],
            'schedule.default_end_time' => ['required_with:schedule', 'date_format:H:i', 'after:schedule.default_start_time'],
            'schedule.anchor_date' => ['nullable', 'required_if:schedule.frequency,weekly,monthly', 'date_format:Y-m-d'],
            'schedule.dates' => ['nullable', 'array', 'max:' . self::MAX_UPCOMING_DATES],
            'schedule.dates.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
            'schedule.dates.*.start_time' => ['nullable', 'date_format:H:i', 'required_with:schedule.dates.*.end_time'],
            'schedule.dates.*.end_time' => ['nullable', 'date_format:H:i', 'required_with:schedule.dates.*.start_time', 'after:schedule.dates.*.start_time'],
        ];
    }

    public function scheduleInput(): ?EventScheduleInputData
    {
        $schedule = $this->validated('schedule');

        if (! is_array($schedule)) {
            return null;
        }

        return EventScheduleInputData::from($schedule);
    }

    protected function validateScheduleDates(Validator $validator): void
    {
        if (! $this->hasScheduleInput() || $validator->errors()->isNotEmpty()) {
            return;
        }

        $schedule = $this->input('schedule');

        try {
            $today = CarbonImmutable::now($schedule['timezone'])->startOfDay();
        } catch (Throwable) {
            return;
        }

        $latest = $today->addMonths(self::MAX_MONTHS_AHEAD)->toDateString();
        $upcoming = collect($schedule['dates'] ?? [])
            ->pluck('date')
            ->filter(fn (string $date) => $date >= $today->toDateString());

        if ($upcoming->contains(fn (string $date) => $date > $latest)) {
            $validator->errors()->add('schedule.dates', 'Dates can be up to 12 months ahead.');
        }

        $anchor = $schedule['anchor_date'] ?? null;

        if ($anchor && $anchor > $latest) {
            $validator->errors()->add('schedule.anchor_date', 'The first date can be up to 12 months ahead.');
        }

        if (! $this->scheduleMustHaveUpcomingDate()) {
            return;
        }

        $generatesDates = in_array($schedule['frequency'], [FrequencyEnum::WEEKLY->value, FrequencyEnum::MONTHLY->value], true)
            && empty($schedule['dates'])
            && $anchor;

        if ($upcoming->isEmpty() && ! $generatesDates) {
            $validator->errors()->add('schedule.dates', 'Add at least one upcoming date.');
        }
    }

    /**
     * Publishing a Fire that already has a schedule, without sending one, still needs
     * an upcoming date.
     */
    protected function validateStoredSchedule(Validator $validator, ?Event $event): void
    {
        if ($this->hasScheduleInput() || ! $event instanceof Event) {
            return;
        }

        if (! ScheduleReadiness::canPublish($event)) {
            $validator->errors()->add('schedule', ScheduleReadiness::MISSING_DATE_MESSAGE);
        }
    }
}
