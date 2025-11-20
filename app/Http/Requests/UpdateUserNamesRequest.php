<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserNamesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow access via signed URL (verified by signed middleware)
        return true;
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
        ];
    }

    /**
     * Get the custom messages for the validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'given_name.required' => 'First name is required.',
            'given_name.min' => 'First name must be at least 2 characters.',
            'family_name.required' => 'Last name is required.',
            'family_name.min' => 'Last name must be at least 2 characters.',
        ];
    }
}
