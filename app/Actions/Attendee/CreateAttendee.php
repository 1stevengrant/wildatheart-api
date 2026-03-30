<?php

namespace App\Actions\Attendee;

use App\Models\Event;
use App\Models\Attendee;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAttendee
{
    use AsAction;

    public function handle(Event $event, array $data): Attendee
    {
        return $event->attendees()->create([
            'user_id' => $event->user->id,
            'contact_user_id' => $data['contact_user_id'] ?? null,
            'given_name' => $data['given_name'],
            'family_name' => $data['family_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => isset($data['country']) ? getCountryCodeFromCountryName($data['country']) : null,
            'country_code' => isset($data['country']) ? getCountryCodeFromCountryName($data['country']) : null,
            'marketing_updates' => $data['marketing_updates'] ?? false,
        ]);
    }
}
