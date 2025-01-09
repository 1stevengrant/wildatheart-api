<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MaximumCharacterCountWithoutHtml;
use App\Rules\MinimumCharacterCountWithoutHtml;

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
            'country' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'registration_url' => ['nullable', 'string', 'sometimes', 'url'],
            'description' => [
                'sometimes',
                'string',
                new MinimumCharacterCountWithoutHtml,
                new MaximumCharacterCountWithoutHtml(500),
            ],
        ];

        $rules['number_of_items_in_material_order'] = $this->route('eventType')?->hasMaterialsOrder()
            ? ['required', 'numeric']
            : ['nullable', 'numeric'];

        return $rules;
    }
}
