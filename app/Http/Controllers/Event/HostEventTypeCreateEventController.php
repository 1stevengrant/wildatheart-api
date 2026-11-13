<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\EventType;
use App\Support\EventDateHelper;
use App\Actions\Event\ApproveEvent;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;
use App\Actions\Event\SendMaterialsOrderToZapier;
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

    public function store(EventRequest $request, EventType $eventType)
    {
        // Redirect Fire events to the simple flow
        if ($eventType->name === EventType::FIRES) {
            return to_route('host.fire');
        }

        if ($eventType->name === EventType::CAPTIVATING_FIRE) {
            return to_route('host.captivating-fire');
        }

        // Prevent duplicate event creation within a short time window
        if (auth()->user()->events()->where('created_at', '>', now()->subSeconds(10))->exists()) {
            return back()->withErrors([
                'duplicate' => 'An event was just created. Please wait a moment before creating another.',
            ]);
        }

        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
            eventTypeName: $eventType->name,
        );

        $event = auth()->user()->events()->create([
            'event_type_id' => $eventType->id,
            'name' => $request->name,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'region' => $request->region,
            'district' => $request->district,
            'locality' => $request->locality,
            'neighborhood' => $request->neighborhood,
            'street_address' => $request->street_address,
            'is_private' => $request->is_private,
            'state' => $request->state,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'sex' => $eventType->sex,
            'number_of_items_in_material_order' => $request->number_of_items_in_material_order,
        ]);

        ApproveEvent::run(event: $event);

        SendMaterialsOrderToZapier::run(
            event: $event,
            eventType: $eventType,
            user: auth()->user(),
            quantity: $request->number_of_items_in_material_order,
        );

        return to_route('my.events.active', ['event' => $event]);
    }
}
