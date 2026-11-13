<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeScheduleController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function __invoke(EventType $eventType, Request $request)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide.before');

        return inertia('Event/ScheduleEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => $this->eventCreationStepHandlerService->eventSteps(),
        ]);
    }
}
