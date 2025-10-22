<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
        ];

        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');

        $rules['number_of_items_in_material_order'] = $this->route('eventType')?->hasMaterialsOrder()
            ? ['required', 'string', 'in:' . implode(',', $validValues)]
            : ['nullable', 'string', 'in:' . implode(',', $validValues)];

        $eventType = $this->route('eventType');
        $rules['day_count'] = $eventType?->name === EventType::CAPTIVATING_CORE_RETREAT
            ? ['required', 'integer', 'in:1,4']
            : ['nullable', 'integer', 'in:1,4'];

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day_count.required' => 'We need to know how many days your retreat will run for.',
            'day_count.in' => 'We need to know how many days your retreat will run for.',
        ];
    }
}
