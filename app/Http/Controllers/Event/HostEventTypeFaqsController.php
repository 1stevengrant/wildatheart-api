<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeFaqsController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function __invoke(EventType $eventType, Request $request)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'schedule');

        return inertia('Event/FaqsEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }
}
