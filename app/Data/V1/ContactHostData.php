<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;

class ContactHostData extends Data
{
    public function __construct(
        public string $given_name,
        public string $family_name,
        public string $email,
        public string $phone,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public bool $opted_into_marketing,
        public bool $agreed_to_terms,
        public string $contact_token,
    ) {}

    public static function rules(): array
    {
        return [
            'given_name' => ['required', 'string', 'min:2', 'max:255'],
            'family_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['required', 'min:9', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'opted_into_marketing' => ['required', 'boolean'],
            'agreed_to_terms' => ['required', 'accepted'],
            'contact_token' => ['required', 'string'],
        ];
    }

    public static function messages(): array
    {
        return [
            'given_name.required' => 'Your first name is required',
            'given_name.min' => 'Your first name must be at least 2 characters',
            'given_name.max' => 'Your first name cannot exceed 255 characters',
            'family_name.required' => 'Your last name is required',
            'family_name.min' => 'Your last name must be at least 2 characters',
            'family_name.max' => 'Your last name cannot exceed 255 characters',
            'email.required' => 'Your email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Your email address cannot exceed 255 characters',
            'phone.required' => 'Your phone number is required',
            'phone.min' => 'Your phone number must be at least 9 digits',
            'phone.max' => 'Your phone number cannot exceed 20 digits',
            'opted_into_marketing.required' => 'Please indicate your communication preferences',
            'opted_into_marketing.boolean' => 'Invalid value for communication preferences',
            'agreed_to_terms.required' => 'You must agree to the terms',
            'agreed_to_terms.accepted' => 'You must agree to the terms',
            'contact_token.required' => 'A verification token is required',
        ];
    }
}
