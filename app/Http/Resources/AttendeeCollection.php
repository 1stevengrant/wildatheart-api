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
            'family_name' => $attendee->family_name,
            'given_name' => $attendee->given_name,
            'event_id' => $attendee->event_id,
            'email' => $attendee->email,
            'deleted_at' => $attendee->deleted_at,
        ]);
    }
}
