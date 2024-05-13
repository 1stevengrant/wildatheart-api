<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Http\Requests\FireEventRequest;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeCreateEventController extends Controller
{
    public function index(EventType $eventType)
    {
        return inertia('Event/CreateEventEventTypePage', [
            'eventType' => $eventType,
        ]);
    }

    public function store(Request $request, EventType $eventType)
    {
        if ($eventType->name === EventType::FIRES) {
            $request->validate((new FireEventRequest())->rules());
        } else {
            $request->validate((new EventRequest())->rules());
        }

        $event = auth()->user()->events()->create([
            'event_type_id' => $eventType->id,
            'name' => $request->name,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'region' => $request->region,
            'district' => $request->district,
            'locality' => $request->locality,
            'neighborhood' => $request->neighborhood,
            'street_address' => $request->street_address,
            'is_private' => $request->is_private,
            'state' => $request->state,
            'start_date' => $request->start_date,
        ]);

        return redirect()->route('my.events', ['event' => $event]);
    }
}
