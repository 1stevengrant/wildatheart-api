<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeScheduleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType, Request $request)
    {
        return inertia('Event/ScheduleEventTypePage', [
            'eventType' => $eventType,
        ]);
    }
}
