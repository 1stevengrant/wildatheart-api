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
        public ?string $given_name,
        public ?string $family_name,
        public string $fullname,
        public string $email,
        public bool $opted_into_marketing,
        public ?string $image,
        public int $profile_step,
        public ?string $phone,
        public ?string $city,
        public ?string $state,
        public ?string $postal_code,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            given_name: $user->given_name,
            family_name: $user->family_name,
            fullname: $user->fullname,
            email: $user->email,
            opted_into_marketing: $user->opted_into_marketing ?? false,
            image: $user->image,
            profile_step: $user->profile_step ?? 0,
            phone: $user->phone,
            city: $user->city,
            state: $user->state,
            postal_code: $user->postal_code,
        );
    }
}
