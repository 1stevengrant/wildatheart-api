<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Http\Requests\FireEventRequest;
use App\Models\EventType;
use App\Services\EventCreationStepHandlerService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HostEventTypeCreateEventController extends Controller
{
    protected EventCreationStepHandlerService $eventCreationStepHandlerService;

    public function __construct(EventCreationStepHandlerService $eventCreationStepHandlerService)
    {
        $this->eventCreationStepHandlerService = $eventCreationStepHandlerService;
    }

    public function index(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('mailing_address');

        return inertia('Event/CreateEventEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }

    public function store(Request $request, EventType $eventType)
    {
        ray(session('event_steps'));
        if ($eventType->name === EventType::FIRES) {
            $request->validate((new FireEventRequest)->rules());
        } else {
            $request->validate((new EventRequest)->rules());
        }

        if (str_contains(strtolower($eventType->name), 'retreat')) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays(3)->format('Y-m-d'),
            ]);
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
            'end_date' => $request->end_date,
            'sex' => determineSexOfEvent($eventType),
        ]);

        return redirect()->route('my.events.active', ['event' => $event]);
    }
}
