<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AttendeeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(fn ($attendee) => [
            'id' => $attendee->id,
            'full_name' => $attendee->full_name,
            'family_name' => $attendee->family_name,
            'given_name' => $attendee->given_name,
            'phone' => $attendee->phone,
            'event_id' => $attendee->event_id,
            'event_type_name' => $attendee->event->eventType->name,
            'event_name' => $attendee->event->name,
            'email' => $attendee->email,
            'deleted_at' => $attendee->deleted_at,
        ]);
    }
}
