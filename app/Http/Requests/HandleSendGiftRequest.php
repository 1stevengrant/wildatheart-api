<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandleSendGiftRequest extends FormRequest
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
            'country' => 'required|max:255',
            'given_name' => 'required|max:255|min:2',
            'family_name' => 'required|max:255|min:2',
            'company' => 'nullable',
            'street_address' => 'required|max:255',
            'address2' => 'nullable',
            'city' => 'required|max:255',
            'state' => ['required'],
            'zip' => 'required|max:20|min:4',
            'phone' => 'required',
        ];
    }
}
