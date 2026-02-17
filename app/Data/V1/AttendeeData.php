<?php

namespace App\Data\V1;

use App\Models\Attendee;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AttendeeData extends Data
{
    public function __construct(
        public int $id,
        public string $event_id,
        public string $given_name,
        public string $family_name,
        public string $email,
        public string $phone,
        public string $city,
        public string $state,
        public string $country,
        public string $country_code,
        public bool $marketing_updates,
        public string $created_at,
    ) {}

    public static function fromAttendee(Attendee $attendee): self
    {
        return new self(
            id: $attendee->id,
            event_id: $attendee->event_id,
            given_name: $attendee->given_name,
            family_name: $attendee->family_name,
            email: $attendee->email,
            phone: $attendee->phone,
            city: $attendee->city,
            state: $attendee->state,
            country: $attendee->country,
            country_code: $attendee->country_code,
            marketing_updates: $attendee->marketing_updates,
            created_at: $attendee->created_at->format('M. j, Y'),
        );
    }
}
