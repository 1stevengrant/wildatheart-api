<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Validation\Validator;
use App\Support\EventTypeDateSelector;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\ValidatesEventSchedule;

class SaveEventDraftRequest extends FormRequest
{
    use ValidatesEventSchedule;

    public function authorize(): bool
    {
        $eventId = $this->input('event_id');

        if (! $eventId) {
            return true;
        }

        $event = Event::find($eventId);

        return $event instanceof Event
            && $event->isOwnedOrManagedBy($this->user());
    }

    public function rules(): array
    {
        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');
        $frequencyValues = EventTypeDateSelector::allowedFrequencies($this->route('eventType')?->group_type);

        return array_merge([
            'event_id' => ['nullable', 'string', 'uuid'],
            'location' => ['nullable', 'array'],
            'location.address' => ['nullable', 'string'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'name' => ['nullable', 'string', 'max:' . ($this->route('eventType')?->maxEventNameLength() ?? EventType::MAX_EVENT_NAME_LENGTH)],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'frequency' => ['nullable', 'string', 'in:' . implode(',', $frequencyValues)],
            'is_private' => ['nullable', 'boolean'],
            'number_of_items_in_material_order' => ['nullable', 'string', 'in:' . implode(',', $validValues)],
            'co_host_emails' => ['nullable', 'array'],
            'co_host_emails.*' => ['email'],
            'event_info' => ['nullable', 'string', 'max:5000'],
            'expected_attendees' => ['nullable', 'string', 'max:50'],
            'show_address_preference' => ['nullable', 'string', 'in:exact,approximate,hide'],
            'mail_to_preference' => ['nullable', 'string', 'in:mailToEventAddress,mailToDifferentAddress,doNotMailResources'],
            'mail_to_location' => ['nullable', 'array'],
            'mail_to_location.address' => ['nullable', 'string'],
            'mail_to_location.latitude' => ['nullable', 'numeric'],
            'mail_to_location.longitude' => ['nullable', 'numeric'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:255'],
        ], $this->scheduleRules());
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateScheduleDates($validator),
        ];
    }

    public function existingEvent(): ?Event
    {
        $eventId = $this->input('event_id');

        if (! $eventId) {
            return null;
        }

        return Event::find($eventId);
    }

    protected function scheduleMustHaveUpcomingDate(): bool
    {
        return $this->existingEvent()?->approved_at !== null;
    }

    /**
     * Prepare the data for validation.
     * Merge nested location fields into top-level for compatibility with Event model.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->filled('location')) {
            $loc = $this->input('location');
            if (is_array($loc)) {
                $merge['name'] = $merge['name'] ?? $loc['address'] ?? null;
                $merge['latitude'] = $merge['latitude'] ?? $loc['latitude'] ?? null;
                $merge['longitude'] = $merge['longitude'] ?? $loc['longitude'] ?? null;
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }

        if ($this->has('co_host_emails') && ! is_array($this->co_host_emails)) {
            $this->merge(['co_host_emails' => []]);
        }
    }
}
