<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'given_name' => [
                'required',
                'string',
                'max:255',
            ],
            'family_name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:255',
            ],
            'new_email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->id()),
                Rule::unique('users', 'pending_email')->ignore(auth()->id()),
            ],
            'image' => [
                'nullable',
                'image',
                'max:1024',
            ],
            'opted_into_marketing' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'new_email.unique' => 'This email address is already in use.',
        ];
    }
}
