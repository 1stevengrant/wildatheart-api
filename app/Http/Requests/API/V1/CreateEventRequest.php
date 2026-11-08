<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Validation\Validator;
use App\Support\EventTypeDateSelector;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasLocationRules;
use App\Http\Requests\Concerns\ValidatesEventSchedule;

class CreateEventRequest extends FormRequest
{
    use HasLocationRules;
    use ValidatesEventSchedule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventType = $this->route('eventType');
        $rules = [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => [$this->presenceRuleForLegacyDateFields(null), 'date'],
            'is_private' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');
        $frequencyValues = array_map(fn (FrequencyEnum $case) => $case->value, FrequencyEnum::cases());

        $rules['number_of_items_in_material_order'] = $eventType?->hasMaterialsOrder()
            ? ['required', 'string', 'in:' . implode(',', $validValues)]
            : ['nullable', 'string', 'in:' . implode(',', $validValues)];

        $rules['frequency'] = $eventType && EventTypeDateSelector::requiresFrequency($eventType->group_type)
            ? [$this->presenceRuleForLegacyDateFields(null), 'string', 'in:' . implode(',', $frequencyValues)]
            : ['nullable', 'string', 'in:' . implode(',', $frequencyValues)];

        return array_merge($this->getLocationRules(), $rules, $this->scheduleRules());
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

    protected function scheduleMustHaveUpcomingDate(): bool
    {
        return true;
    }
}
