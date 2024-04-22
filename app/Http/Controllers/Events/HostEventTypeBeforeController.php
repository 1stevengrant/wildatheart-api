<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeBeforeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType)
    {
        return inertia('Event/BeforeEventTypePage', [
            'eventType' => $eventType,
        ]);
    }
}
