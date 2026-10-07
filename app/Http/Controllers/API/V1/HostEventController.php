<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\EventData;
use App\Models\EventType;
use App\Support\EventDateHelper;
use Illuminate\Http\JsonResponse;
use App\Actions\Event\DeleteEvent;
use App\Actions\Event\ApproveEvent;
use App\Http\Controllers\Controller;
use App\Actions\Event\SaveEventDraft;
use App\Support\EventTypeDateSelector;
use App\Actions\Event\PublishEventDraft;
use App\Http\Requests\API\V1\CreateEventRequest;
use App\Actions\Event\SendMaterialsOrderToZapier;
use App\Http\Requests\API\V1\PublishEventRequest;
use App\Actions\Event\SendPendingCoHostInvitations;
use App\Http\Requests\API\V1\SaveEventDraftRequest;
use App\Http\Requests\API\V1\ApproveHostEventRequest;
use App\Http\Requests\API\V1\DestroyHostEventRequest;
use App\Http\Requests\API\V1\UnpublishHostEventRequest;
use App\Http\Requests\API\V1\UpdateHostEventVisibilityRequest;

class HostEventController extends Controller
{
    public function index(): JsonResponse
    {
        $baseQuery = Event::query()
            ->controllableByUser(auth()->user())
            ->with(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])
            ->orderByRaw('start_date < NOW()')
            ->orderBy('start_date');

        $listed = (clone $baseQuery)
            ->published()
            ->get()
            ->map(fn (Event $event) => EventData::fromModel($event))
            ->values();

        $drafts = (clone $baseQuery)
            ->unpublished()
            ->get()
            ->map(fn (Event $event) => EventData::fromModel($event))
            ->values();

        return response()->json([
            'listed' => $listed,
            'drafts' => $drafts,
        ]);
    }

    public function store(CreateEventRequest $request, EventType $eventType): JsonResponse
    {
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
            'frequency' => EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? $request->frequency
                : null,
            'sex' => $eventType->sex,
            'number_of_items_in_material_order' => $request->number_of_items_in_material_order,
        ]);

        SendMaterialsOrderToZapier::run(
            event: $event,
            eventType: $eventType,
            user: auth()->user(),
            quantity: $eventType->materialOrderQuantity($request->number_of_items_in_material_order),
        );

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }

    public function storeDraft(SaveEventDraftRequest $request, EventType $eventType): JsonResponse
    {
        $event = SaveEventDraft::run($request, $eventType, auth()->user());

        return response()->json([
            'success' => true,
            'event' => $event->fresh(['managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser']),
        ]);
    }

    public function publish(PublishEventRequest $request, EventType $eventType, Event $event): JsonResponse
    {
        $publishedEvent = PublishEventDraft::run($request, $eventType, $event, auth()->user());

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($publishedEvent),
        ]);
    }

    public function destroy(DestroyHostEventRequest $request, Event $event): JsonResponse
    {
        DeleteEvent::run($event);

        return response()->json([
            'success' => true,
        ]);
    }

    public function unpublish(UnpublishHostEventRequest $request, Event $event): JsonResponse
    {
        $event->unpublish();

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])),
        ]);
    }

    public function approve(ApproveHostEventRequest $request, Event $event): JsonResponse
    {
        ApproveEvent::run($event);
        // Invites added while it was a draft wait for publishing, same as the wizard's publish.
        SendPendingCoHostInvitations::run($event->fresh(['pendingManagerInvitations']), auth()->user());

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])),
        ]);
    }

    public function updateVisibility(UpdateHostEventVisibilityRequest $request, Event $event): JsonResponse
    {
        $event->is_private = $request->boolean('is_private');
        $event->save();

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])),
        ]);
    }
}
