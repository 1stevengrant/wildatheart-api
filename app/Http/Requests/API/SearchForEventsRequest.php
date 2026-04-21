<?php

namespace App\Http\Requests\API;

use App\Enums\EventSexEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SearchForEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array|string> */
    public function rules(): array
    {
        return [
            'sw_latitude' => ['required', 'numeric', 'between:-90,90'],
            'sw_longitude' => ['required', 'numeric'],
            'ne_latitude' => ['required', 'numeric', 'between:-90,90'],
            'ne_longitude' => ['required', 'numeric'],

            'sex' => ['nullable', 'string', Rule::enum(EventSexEnum::class)],
            'type_group' => ['nullable', 'string'],
            'types' => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'sw_latitude.between' => 'The southwest latitude must be between -90 and 90 degrees.',
            'ne_latitude.between' => 'The northeast latitude must be between -90 and 90 degrees.',
            'types.regex' => 'The types must be a comma-separated list of numbers.',
        ];
    }
}
