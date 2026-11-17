<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;
use App\Actions\Event\CreateWebEvent;
use App\Exceptions\EventCreationException;
use App\DataTransferObjects\EventCreationData;
use Illuminate\Container\Attributes\CurrentUser;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeCreateEventController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function index(EventType $eventType)
    {
        // Redirect Fire events to the simple flow
        if ($eventType->name === EventType::FIRES) {
            return to_route('host.fire');
        }

        if ($eventType->name === EventType::CAPTIVATING_FIRE) {
            return to_route('host.captivating-fire');
        }

        $this->eventCreationStepHandlerService->markStepComplete('mailing_address');

        return inertia('Event/CreateEventEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => $this->eventCreationStepHandlerService->eventSteps(),
            'materialOrderItems' => Event::MATERIAL_ORDER_ITEMS,
        ]);
    }

    public function store(EventRequest $request, EventType $eventType, #[CurrentUser] User $user)
    {
        // Redirect Fire events to the simple flow
        if ($eventType->name === EventType::FIRES) {
            return to_route('host.fire');
        }

        if ($eventType->name === EventType::CAPTIVATING_FIRE) {
            return to_route('host.captivating-fire');
        }

        try {
            $event = CreateWebEvent::run(
                host: $user,
                eventType: $eventType,
                data: EventCreationData::fromRequest($request, $eventType),
            );
        } catch (EventCreationException $exception) {
            return back()->withErrors(['duplicate' => $exception->getMessage()]);
        }

        return to_route('my.events.active', ['event' => $event]);
    }
}
