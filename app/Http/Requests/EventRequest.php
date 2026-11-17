<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasLocationRules;
use Illuminate\Contracts\Validation\ValidationRule;

class EventRequest extends FormRequest
{
    use HasLocationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_private' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');

        $rules['number_of_items_in_material_order'] = $this->route('eventType')?->hasMaterialsOrder()
            ? ['required', 'string', 'in:' . implode(',', $validValues)]
            : ['nullable', 'string', 'in:' . implode(',', $validValues)];

        return array_merge($this->getLocationRules(), $rules);
    }
}
