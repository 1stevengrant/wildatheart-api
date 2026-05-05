<?php

namespace App\Http\Controllers;

use App\Models\EventType;

class EventGuideController extends Controller
{
    public function __invoke(EventType $eventType)
    {
        return inertia('EventGuidePage', [
            'eventType' => $eventType,
        ]);
    }
}
