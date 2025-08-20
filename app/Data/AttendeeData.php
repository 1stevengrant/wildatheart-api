<?php

namespace App\Data;

use App\Models\Attendee;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AttendeeData extends Data
{
    public function __construct(
        public int $id,
        public string $event_id,
        public ?int $user_id,
        public string $family_name,
        public string $given_name,
        public string $email,
        public string $country,
        public string $country_code,
        public string $phone,
        public string $city,
        public string $state,
        public bool $marketing_updates,
        public ?string $contacted_on,
        public string $created_at,
        public string $updated_at,
        public ?string $event_type_name,
        public ?string $event_name,
        public string $fullname,
    ) {}

    public static function fromModel(Attendee $attendee): self
    {
        return new self(
            id: $attendee->id,
            event_id: $attendee->event_id,
            user_id: $attendee->user_id,
            family_name: $attendee->family_name,
            given_name: $attendee->given_name,
            email: $attendee->email,
            country: $attendee->country,
            country_code: $attendee->country_code,
            phone: $attendee->phone,
            city: $attendee->city,
            state: $attendee->state,
            marketing_updates: $attendee->marketing_updates,
            contacted_on: $attendee->contacted_on,
            created_at: $attendee->created_at,
            updated_at: $attendee->updated_at,
            event_type_name: $attendee->event->eventType?->name,
            event_name: $attendee->event->display_name,
            fullname: $attendee->fullname
        );
    }
}
