<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventGuideController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(\App\Models\EventType $eventType)
    {
        return inertia('EventGuidePage', [
            'eventType' => $eventType,
        ]);
    }
}
