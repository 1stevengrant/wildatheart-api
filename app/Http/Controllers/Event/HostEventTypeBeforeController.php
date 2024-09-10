<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeBeforeController extends Controller
{
    protected EventCreationStepHandlerService $eventCreationStepHandlerService;

    public function __construct(EventCreationStepHandlerService $eventCreationStepHandlerService)
    {
        $this->eventCreationStepHandlerService = $eventCreationStepHandlerService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'welcome');

        return inertia('Event/BeforeEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
