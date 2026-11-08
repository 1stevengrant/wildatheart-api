<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Validation\Validator;
use App\Support\EventTypeDateSelector;
use App\Http\Requests\Concerns\HasLocationRules;

class PublishEventRequest extends SaveEventDraftRequest
{
    use HasLocationRules;

    public function authorize(): bool
    {
        /** @var Event|null $event */
        $event = $this->route('event');
        $eventType = $this->route('eventType');

        if (! $event instanceof Event || ! $eventType) {
            return false;
        }

        return $event->user_id === $this->user()->id
            && $event->approved_at === null
            && $event->event_type_id === $eventType->id;
    }

    public function rules(): array
    {
        $eventType = $this->route('eventType');
        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');
        $frequencyValues = array_map(fn (FrequencyEnum $case) => $case->value, FrequencyEnum::cases());

        $publishRules = [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => [$this->presenceRuleForLegacyDateFields($this->existingEvent()), 'date'],
            'number_of_items_in_material_order' => $eventType?->hasMaterialsOrder()
                ? ['required', 'string', 'in:' . implode(',', $validValues)]
                : ['nullable', 'string', 'in:' . implode(',', $validValues)],
            'frequency' => $eventType && EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? [$this->presenceRuleForLegacyDateFields($this->existingEvent()), 'string', 'in:' . implode(',', $frequencyValues)]
                : ['nullable', 'string', 'in:' . implode(',', $frequencyValues)],
        ];

        return array_merge(
            $this->getLocationRules(),
            parent::rules(),
            $publishRules,
        );
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateScheduleDates($validator),
            fn (Validator $validator) => $this->validateStoredSchedule($validator, $this->existingEvent()),
        ];
    }

    protected function scheduleMustHaveUpcomingDate(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        /** @var Event $event */
        $event = $this->route('event');

        $this->merge([
            'event_id' => $event->id,
        ]);
    }
}
