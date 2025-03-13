<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'given_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'family_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'phone' => [
                'required',
                'max:20',
                'min:9',
            ],
            'opted_into_marketing' => [
                'required',
                'boolean',
            ],
            'image' => [
                'nullable',
                'image',
                'max:5120',
                'mimes:jpg,jpeg,png',
            ],
        ];
    }
}
