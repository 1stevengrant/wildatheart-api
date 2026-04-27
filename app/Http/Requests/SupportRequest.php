<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SupportRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns',
                'max:255',
            ],
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
            'subject' => [
                'required',
                'max:255',
            ],
            'description' => [
                'required',
            ],
        ];
    }
}
