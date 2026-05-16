<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class MailingAddressRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => [
                'nullable',
                'integer',
            ],
            'given_name' => [
                'required',
                'string',
            ],
            'family_name' => [
                'required',
                'string',
            ],
            'company' => [
                'nullable',
                'string',
            ],
            'street_address' => [
                'required',
                'string',
            ],
            'address2' => [
                'nullable',
                'string',
            ],
            'city' => [
                'required',
                'string',
            ],
            'state' => [
                'required',
                'string',
            ],
            'zip' => [
                'required',
                'string',
            ],
            'country' => [
                'required',
                'string',
            ],
            'phone' => [
                'nullable',
                'string',
            ],
            'is_primary' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
