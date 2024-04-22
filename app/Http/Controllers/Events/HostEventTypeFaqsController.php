<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeFaqsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType, Request $request)
    {
        return inertia('Event/FaqsEventTypePage', [
            'eventType' => $eventType,
        ]);
    }
}
