<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Support\EventDateHelper;
use App\Support\EventTypeDateSelector;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\API\V1\SaveEventDraftRequest;

class SaveEventDraft
{
    use AsAction;

    public function handle(SaveEventDraftRequest $request, EventType $eventType, User $host): Event
    {
        $eventId = $request->validated('event_id');

        if ($eventId) {
            $event = $this->updateExistingDraft($request, $eventType, $host, $eventId);
        } else {
            $event = $this->createNewDraft($request, $eventType, $host);
        }

        if ($request->has('co_host_emails')) {
            SyncCoHostInvitations::run($event, $request->validated('co_host_emails') ?? [], $host);
        }

        return $event;
    }

    private function updateExistingDraft(
        SaveEventDraftRequest $request,
        EventType $eventType,
        User $host,
        string $eventId,
    ): Event {
        $event = Event::findOrFail($eventId);

        if ($request->filled('start_date')) {
            EventDateHelper::adjustEndDateForRetreat(
                request: $request,
                groupType: $eventType->group_type,
                eventTypeName: $eventType->name,
            );
        }

        $event->fill($this->buildPayload($request, $eventType));
        $event->save();

        return $event;
    }

    private function createNewDraft(
        SaveEventDraftRequest $request,
        EventType $eventType,
        User $host,
    ): Event {
        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
            eventTypeName: $eventType->name,
        );

        return $host->events()->create(
            array_merge(
                $this->buildPayload($request, $eventType),
                [
                    'event_type_id' => $eventType->id,
                    'sex' => $eventType->sex,
                    'approved_at' => null,
                ]
            )
        );
    }

    private function buildPayload(SaveEventDraftRequest $request, EventType $eventType): array
    {
        $payload = [
            'name' => $request->filled('name') ? $request->name : null,
            'latitude' => $request->filled('latitude') ? $request->latitude : null,
            'longitude' => $request->filled('longitude') ? $request->longitude : null,
            'start_date' => $request->filled('start_date') ? $request->start_date : null,
            'end_date' => $request->filled('end_date') ? $request->end_date : null,
            'frequency' => EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? ($request->filled('frequency') ? $request->frequency : null)
                : null,
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
