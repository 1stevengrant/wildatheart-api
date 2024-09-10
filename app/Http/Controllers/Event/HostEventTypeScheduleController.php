<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Services\EventCreationStepHandlerService;
use Illuminate\Http\Request;

class HostEventTypeScheduleController extends Controller
{
    protected EventCreationStepHandlerService $eventCreationStepHandlerService;

    public function __construct(EventCreationStepHandlerService $eventCreationStepHandlerService)
    {
        $this->eventCreationStepHandlerService = $eventCreationStepHandlerService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType, Request $request)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'before');

        return inertia('Event/ScheduleEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
