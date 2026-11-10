<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Support\Schedule\ScheduleReadiness;

/**
 * Whether a saved event has everything publishing requires — the same requirements the publish
 * form enforces (PublishEventRequest), applied to the stored event so one-click publishing from
 * the dashboard can't list an incomplete draft.
 */
class EventPublishReadiness
{
    /**
     * @return array<string, string> First error message per field; empty when ready to publish.
     */
    public static function errors(Event $event): array
    {
        $eventType = $event->eventType;
        $groupType = $eventType?->group_type;

        // Raw values: the latitude/longitude accessors round a missing coordinate to 0.0.
        $data = [
            'latitude' => $event->getRawOriginal('latitude'),
            'longitude' => $event->getRawOriginal('longitude'),
            'start_date' => $event->start_date?->toDateString(),
            'frequency' => $event->frequency?->value,
            'number_of_items_in_material_order' => $event->number_of_items_in_material_order,
        ];

        $rules = [
            'latitude' => ['required'],
            'longitude' => ['required'],
            'start_date' => ['required', 'date'],
            'frequency' => EventTypeDateSelector::requiresFrequency($groupType)
                ? ['required', Rule::in(EventTypeDateSelector::allowedFrequencies($groupType))]
                : ['nullable'],
            'number_of_items_in_material_order' => $eventType?->requiresMaterialOrderQuantity()
                ? ['required']
                : ['nullable'],
        ];

        $messages = [
            'latitude.required' => 'Add a location before publishing.',
            'longitude.required' => 'Add a location before publishing.',
            'start_date.required' => 'Add a date before publishing.',
            'frequency.required' => 'Choose how often this event happens before publishing.',
            'frequency.in' => 'Choose a supported frequency before publishing.',
            'number_of_items_in_material_order.required' => 'Choose the expected number of attendees before publishing.',
        ];

        // A scheduled Fire's dates and frequency live on its schedule, not on the event.
        $hasSchedule = $event->isFire() && $event->schedule()->exists();

        if ($hasSchedule) {
            unset($rules['start_date'], $rules['frequency']);
        }

        $errors = collect(Validator::make($data, $rules, $messages)->errors()->messages())
            ->map(fn (array $fieldMessages) => $fieldMessages[0])
            ->all();

        if ($hasSchedule && ! ScheduleReadiness::canPublish($event)) {
            $errors['schedule'] = ScheduleReadiness::MISSING_DATE_MESSAGE;
        }

        return $errors;
    }
}
