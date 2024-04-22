<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

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
