<?php

namespace App\Http\Requests\API\V1;

use App\Http\Requests\Concerns\HasLocationRules;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    use HasLocationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'is_private' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');

        $rules['number_of_items_in_material_order'] = $this->route('eventType')?->hasMaterialsOrder()
            ? ['required', 'string', 'in:'.implode(',', $validValues)]
            : ['nullable', 'string', 'in:'.implode(',', $validValues)];

        return array_merge($this->getLocationRules(), $rules);
    }
}
