<?php

namespace App\Http\Controllers;

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
}
