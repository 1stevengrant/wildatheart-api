<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeFaqsController extends Controller
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
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'schedule');

        return inertia('Event/FaqsEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
