<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ConnectWithHostData extends Data
{
    public function __construct(
        public string $given_name,
        public string $family_name,
        public string $email,
        public string $confirm_email,
        public string $city,
        public string $state,
        public string $country,
        public string $phone,
        public bool $opted_into_marketing,
    ) {}

    public static function rules(): array
    {
        return [
            'given_name' => ['required', 'string', 'min:2', 'max:255'],
            'family_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns', 'max:255'],
            'confirm_email' => ['required', app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns', 'same:email'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'min:9', 'max:20'],
            'opted_into_marketing' => ['required', 'boolean'],
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
            'confirm_email.required' => 'Please confirm your email address',
            'confirm_email.email' => 'Please enter a valid email address',
            'confirm_email.same' => 'Email addresses must match',
            'city.required' => 'Your city is required',
            'city.max' => 'Your city name cannot exceed 255 characters',
            'state.required' => 'Your state or region is required',
            'state.max' => 'Your state name cannot exceed 255 characters',
            'country.required' => 'Your country is required',
            'country.max' => 'Your country name cannot exceed 255 characters',
            'phone.required' => 'Your phone number is required',
            'phone.min' => 'Your phone number must be at least 9 digits',
            'phone.max' => 'Your phone number cannot exceed 20 digits',
            'opted_into_marketing.required' => 'Please indicate your communication preferences',
            'opted_into_marketing.boolean' => 'Invalid value for communication preferences',
        ];
    }
}
