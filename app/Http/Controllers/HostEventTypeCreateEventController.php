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
        $formRequest = match ($eventType->name) {
            $eventType::FIRES => FireEventRequest::createFrom($request),
            default => EventRequest::createFrom($request),
        };

        ray($formRequest->validated());
    }
}
