<?php

namespace App\Http\Controllers;

class EventGuideController extends Controller
{
    public function __invoke(\App\Models\EventType $eventType)
    {
        return inertia('EventGuidePage', [
            'eventType' => $eventType,
        ]);
    }
}
