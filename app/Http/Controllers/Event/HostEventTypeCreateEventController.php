<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use App\Support\EventDateHelper;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;
use App\Services\EventCreationStepHandlerService;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class HostEventTypeCreateEventController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    public function index(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('mailing_address');

        return inertia('Event/CreateEventEventTypePage', [
            'eventType' => $eventType,
            'eventSteps' => session('event_steps'),
            'materialOrderItems' => Event::MATERIAL_ORDER_ITEMS,
        ]);
    }

    public function store(EventRequest $request, EventType $eventType)
    {
        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
            eventTypeName: $eventType->name
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

        $mailingAddress = auth()->user()->primaryAddress();

        if ($eventType->hasZapier() &&
            $eventType->hasMaterialsOrder() &&
            $request->number_of_items_in_material_order != '0'
        ) {
            $dataTransferObject = new GoogleSheetDataTransferObject(
                webhookUrl: $eventType->zapier_webhook_url,
                eventId: $event->id,
                eventType: $eventType->name,
                email: auth()->user()->email,
                quantity: $request->number_of_items_in_material_order,
                first_name: auth()->user()->given_name,
                last_name: auth()->user()->family_name,
                address_1: $mailingAddress->street_address,
                address_2: $mailingAddress->address2,
                city: $mailingAddress->city,
                state: $mailingAddress->state,
                zip: $mailingAddress->zip,
                country: $mailingAddress->country,
                date: now()->toDateTimeString(),
            );

            PostToZapier::dispatch($dataTransferObject);
        }

        return to_route('my.events.active', ['event' => $event]);
    }
}
