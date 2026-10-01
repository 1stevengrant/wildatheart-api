<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RegisterEventInterestData extends Data
{
    public function __construct(
        public string|Optional|null $given_name,
        public string|Optional|null $family_name,
        public string|Optional|null $phone,
        public string|Optional|null $city,
        public string|Optional|null $state,
        public string|Optional|null $country_code,
        public string|Optional|null $postal_code,
    ) {}

    /**
     * Mirrors UpdateCurrentUserData, except null is accepted and ignored so a
     * blank optional form field never blocks registering interest.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'given_name' => ['nullable', 'string', 'max:100'],
            'family_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country_code' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function profileAttributes(): array
    {
        return array_filter(
            $this->toArray(),
            fn (?string $value) => $value !== null,
        );
    }
}
