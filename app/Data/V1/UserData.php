<?php

namespace App\Data\V1;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $email,
        public ?string $given_name,
        public ?string $family_name,
        public ?string $phone,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $country_code,
        public ?string $postal_code,
        public bool $opted_into_marketing,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id,
            email: $user->email,
            given_name: $user->given_name,
            family_name: $user->family_name,
            phone: $user->phone,
            city: $user->city,
            state: $user->state,
            country: $user->country_code ? getCountryName($user->country_code) : null,
            country_code: $user->country_code,
            postal_code: $user->postal_code,
            opted_into_marketing: (bool) $user->opted_into_marketing,
        );
    }
}
