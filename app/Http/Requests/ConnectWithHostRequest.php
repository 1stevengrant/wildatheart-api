<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConnectWithHostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'family_name' => 'required|string|max:255|min:2',
            'given_name' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255',
            'confirm_email' => 'required|email|same:email',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone' => 'required|min:9|max:20',
            'opted_into_marketing' => 'required|boolean',
        ];
    }
}
