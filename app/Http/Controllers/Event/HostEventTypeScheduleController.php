<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

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
