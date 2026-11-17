<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Http\Requests\UpdateUserProfileRequest;

class ProfileUpdateData extends Data
{
    public function __construct(
        public readonly string $given_name,
        public readonly string $family_name,
        public readonly string $phone,
        public readonly bool $opted_into_marketing,
    ) {}

    public static function fromRequest(UpdateUserProfileRequest $request): self
    {
        return new self(
            given_name: $request->string('given_name')->toString(),
            family_name: $request->string('family_name')->toString(),
            phone: $request->string('phone')->toString(),
            opted_into_marketing: $request->boolean('opted_into_marketing'),
        );
    }
}
