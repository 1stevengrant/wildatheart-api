<?php

namespace App\Http\Controllers\Event;

use App\DTO\GoogleSheetDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Jobs\PostToZapier;
use App\Models\EventType;
use App\Services\EventCreationStepHandlerService;
use App\Support\EventDateHelper;
use Illuminate\Http\Request;

class HostEventTypeCreateEventController extends Controller
{
    protected EventCreationStepHandlerService $eventCreationStepHandlerService;

    public function __construct(EventCreationStepHandlerService $eventCreationStepHandlerService)
    {
        $this->eventCreationStepHandlerService = $eventCreationStepHandlerService;
    }

    public function index(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('mailing_address');

        return inertia('Event/CreateEventEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
        ]);
    }

    public function store(Request $request, EventType $eventType)
    {
        ray(session('event_steps'));

        $request->validate((new EventRequest)->rules());

        EventDateHelper::adjustEndDateForRetreat($request, $eventType->name);

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
            'sex' => determineSexOfEvent($eventType),
            'number_of_items_in_material_order' => $request->number_of_items_in_material_order,
        ]);

        if ($eventType->zapier_webhook_url && $eventType->allow_materials_order) {
            $dto = new GoogleSheetDTO(
                webhookUrl: $eventType->zapier_webhook_url,
                eventId: $event->id,
                eventType: $eventType->name,
                email: auth()->user()->email,
                quantity: $request->number_of_items_in_material_order,
                first_name: auth()->user()->given_name,
                last_name: auth()->user()->family_name,
                address_1: $request->street_address,
                address_2: $request->address_2,
                city: $request->city,
                state: $request->state,
                zip: $request->zip,
                country: $request->country,
                date: now()->toDateTimeString(),
            );

            PostToZapier::dispatch($dto);
        }

        return redirect()->route('my.events.active', ['event' => $event]);
    }
}
