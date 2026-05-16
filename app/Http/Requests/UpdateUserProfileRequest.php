<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

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
     * @return array<string, ValidationRule|array<mixed>|string>
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

    /**
     * Get the custom messages for the validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'given_name.required' => 'First name is required.',
            'family_name.required' => 'Last name is required.',
            'phone.required' => 'The phone number is required.',
            'opted_into_marketing.required' => 'You must indicate whether you opt into marketing communications.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image size must not exceed 5MB.',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png.',
        ];
    }
}
