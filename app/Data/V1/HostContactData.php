<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use App\Models\EventUserInterest;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HostContactData extends Data
{
    public function __construct(
        public string $event_id,
        public int $user_id,
        public string $event_name,
        public string $email,
        public ?string $given_name,
        public ?string $family_name,
        public ?string $phone,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public string $interested_at,
        public ?string $archived_at,
    ) {}

    public static function fromInterest(EventUserInterest $interest): self
    {
        $user = $interest->user;
        $event = $interest->event;

        return new self(
            event_id: (string) $interest->event_id,
            user_id: (int) $interest->user_id,
            event_name: $event->name ?? '',
            email: $user->email,
            given_name: $user->given_name,
            family_name: $user->family_name,
            phone: $user->phone,
            city: $user->city,
            state: $user->state,
            country: $user->country_code ? getCountryName($user->country_code) : null,
            interested_at: $interest->created_at?->toIso8601String() ?? '',
            archived_at: $interest->archived_at?->toIso8601String(),
        );
    }
}
