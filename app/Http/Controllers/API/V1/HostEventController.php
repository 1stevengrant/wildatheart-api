<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use App\Support\EventDateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\CreateEventRequest;
use App\Http\Requests\API\V1\SaveEventDraftRequest;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class HostEventController extends Controller
{
    public function store(CreateEventRequest $request, EventType $eventType)
    {
        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
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

        if (
            $eventType->hasZapier() &&
            $eventType->hasMaterialsOrder() &&
            $request->number_of_items_in_material_order != '0' &&
            $mailingAddress
        ) {
            PostToZapier::dispatch(new GoogleSheetDataTransferObject(
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
            ));
        }

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }

    /**
     * Save an event draft. Accepts partial data; creates or updates an unpublished event.
     * Does not trigger Zapier or external integrations.
     */
    public function storeDraft(SaveEventDraftRequest $request, EventType $eventType)
    {

        $eventId = $request->validated('event_id');

        if ($eventId) {
            $event = Event::where('id', $eventId)
                ->where('user_id', auth()->id())
                ->whereNull('approved_at')
                ->first();

            if (! $event) {
                return response()->json([
                    'message' => 'Draft not found or you do not have permission to update it.',
                ], 404);
            }

            if ($request->filled('start_date')) {
                EventDateHelper::adjustEndDateForRetreat(
                    request: $request,
                    groupType: $eventType->group_type,
                );
            }
            $event->fill($this->draftPayload($request, $eventType));
            $event->save();
        } else {
            EventDateHelper::adjustEndDateForRetreat(
                request: $request,
                groupType: $eventType->group_type,
            );

            $event = auth()->user()->events()->create(
                array_merge(
                    $this->draftPayload($request, $eventType),
                    [
                        'event_type_id' => $eventType->id,
                        'sex' => $eventType->sex,
                        'approved_at' => null,
                    ]
                )
            );
        }

        return response()->json([
            'success' => true,
            'event' => $event->fresh(),
        ]);
    }

    /**
     * Build the event payload from the draft request, including only filled fields.
     */
    private function draftPayload(SaveEventDraftRequest $request, EventType $eventType): array
    {
        $payload = [
            'name' => $request->filled('name') ? $request->name : null,
            'latitude' => $request->filled('latitude') ? $request->latitude : null,
            'longitude' => $request->filled('longitude') ? $request->longitude : null,
            'start_date' => $request->filled('start_date') ? $request->start_date : null,
            'end_date' => $request->filled('end_date') ? $request->end_date : null,
            'is_private' => $request->boolean('is_private', false),
            'number_of_items_in_material_order' => $request->filled('number_of_items_in_material_order')
                ? $request->number_of_items_in_material_order
                : null,
            'description' => $request->filled('event_info') ? $request->event_info : null,
            'city' => $request->input('city'),
            'zip' => $request->input('zip'),
            'country' => $request->input('country'),
            'region' => $request->input('region'),
            'district' => $request->input('district'),
            'locality' => $request->input('locality'),
            'neighborhood' => $request->input('neighborhood'),
            'street_address' => $request->input('street_address'),
            'state' => $request->input('state'),
        ];

        return array_filter($payload, fn ($v) => $v !== null);
    }
}
