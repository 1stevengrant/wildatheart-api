<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeBeforeController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function __invoke(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'welcome');

        return inertia('Event/BeforeEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
