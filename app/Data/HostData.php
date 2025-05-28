<?php

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HostData extends Data
{
    public function __construct(
        public ?string $family_name,
        public ?string $given_name,
        public ?string $md5email,
        public ?string $fullname,
        public ?string $photo = null,
    ) {}

    public static function fromModel(User $user): ?self
    {
        return new self(
            family_name: $user->family_name,
            given_name: $user->given_name,
            md5email: $user->md5_email,
            fullname: $user->fullname,
            photo: $user->profilePhoto(),
        );
    }
}
