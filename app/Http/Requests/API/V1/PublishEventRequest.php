<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use App\Enums\Event\FrequencyEnum;
use App\Support\EventTypeDateSelector;
use App\Http\Requests\Concerns\HasLocationRules;
use App\Http\Requests\Concerns\HasMaterialOrderValidationAttributes;

class PublishEventRequest extends SaveEventDraftRequest
{
    use HasLocationRules;
    use HasMaterialOrderValidationAttributes;

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
            'start_date' => ['required', 'date'],
            'number_of_items_in_material_order' => $eventType?->hasMaterialsOrder()
                ? ['required', 'string', 'in:' . implode(',', $validValues)]
                : ['nullable', 'string', 'in:' . implode(',', $validValues)],
            'frequency' => $eventType && EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? ['required', 'string', 'in:' . implode(',', $frequencyValues)]
                : ['nullable', 'string', 'in:' . implode(',', $frequencyValues)],
        ];

        return array_merge(
            $this->getLocationRules(),
            parent::rules(),
            $publishRules,
        );
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
