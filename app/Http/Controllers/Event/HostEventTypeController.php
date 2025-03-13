<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function __invoke(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->resetEventSteps();

        return inertia('Event/WelcomeEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
