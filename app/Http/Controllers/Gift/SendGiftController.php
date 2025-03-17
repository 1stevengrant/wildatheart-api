<?php

namespace App\Http\Controllers\Gift;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendGiftController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        abort_unless(! $event->hasRequestedGift(), 403, 'You have already requested your gift.');

        return inertia('SendGiftPage', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'longitude' => $event->longitude,
                'latitude' => $event->latitude,
                'display_longitude' => $event->display_longitude ?? $event->longitude,
                'display_latitude' => $event->display_latitude ?? $event->latitude,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'event_type' => $event->eventType,
                'type_group' => $event->eventType->group_type,
                'location' => $event->location,
                'country' => $event->country,
                'host_name' => $event->user->fullname,
            ],
        ]);
    }
}
