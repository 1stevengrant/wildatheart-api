<?php

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserData extends Data
{
    public function __construct(
        public int $id,
        public ?string $given_name,
        public ?string $family_name,
        public string $fullname,
        public string $email,
        public bool $opted_into_marketing,
        public string $image,
        public ?int $profile_step,
        public ?string $phone,
        public ?string $city,
        public ?string $state,
        public ?string $postal_code,
        public bool $has_agreed_to_host_agreement,
        public bool $is_host,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            given_name: $user->given_name,
            family_name: $user->family_name,
            fullname: $user->fullname,
            email: $user->email,
            opted_into_marketing: (bool) $user->opted_into_marketing,
            image: $user->profilePhoto(),
            profile_step: $user->profile_step,
            phone: $user->phone,
            city: $user->city,
            state: $user->state,
            postal_code: $user->postal_code,
            has_agreed_to_host_agreement: $user->hasAgreedToHostAgreement(),
            is_host: $user->isHost(),
        );
    }
}
