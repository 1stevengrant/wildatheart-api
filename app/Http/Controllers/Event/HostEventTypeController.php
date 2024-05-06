<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\EventType;

class HostEventTypeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType)
    {
        return inertia('Event/WelcomeEventTypePage', [
            'eventType' => $eventType,
        ]);
    }
}
