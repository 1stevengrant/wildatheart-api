<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConnectWithHostRequest;
use App\Http\Resources\EventResource;
use App\Jobs\PostAttendeeContactToSlackJob;
use App\Models\Event;

class ConnectWithHostController extends Controller
{
    public function index(Event $event)
    {
        return inertia('ConnectWithHostPage', [
            'event' => EventResource::make($event)->resolve(),
        ]);
    }

    public function store(ConnectWithHostRequest $request, Event $event)
    {
        $request->validated();

        $attendee = $event->attendees()->create([
            'user_id' => $event->host->id,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'city' => $request->city,
            'state' => $request->state,
            'country' => getCountryCodeFromCountryName($request->country),
            'country_code' => getCountryCodeFromCountryName($request->country),
            'marketing_updates' => $request->opted_into_marketing,
        ]);

        activity()
            ->withProperties(['attendee' => $attendee, 'event' => $event])
            ->performedOn($attendee)
            ->causedBy($event)
            ->log('Connected with Host');

        PostAttendeeContactToSlackJob::dispatch($attendee, $event);

        return to_route('view.event', $event)->with(['notifications' => [
            'message' => "Connected with {$event->host->fullName}!",
            'connectedWithHost' => true,
            'type' => 'success',
        ],
        ]);
    }
}
