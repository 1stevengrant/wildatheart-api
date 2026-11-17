<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Models\Event;
use App\Data\EventData;
use App\Models\EventType;
use Illuminate\Http\JsonResponse;
use App\Actions\Event\DeleteEvent;
use App\Actions\Event\ApproveEvent;
use App\Http\Controllers\Controller;
use App\Actions\Event\CreateApiEvent;
use App\Actions\Event\SaveEventDraft;
use App\Actions\Event\PublishEventDraft;
use App\DataTransferObjects\EventCreationData;
use App\Http\Requests\API\V1\CreateEventRequest;
use Illuminate\Container\Attributes\CurrentUser;
use App\Http\Requests\API\V1\PublishEventRequest;
use App\Http\Requests\API\V1\SaveEventDraftRequest;
use App\Http\Requests\API\V1\ApproveHostEventRequest;
use App\Http\Requests\API\V1\DestroyHostEventRequest;
use App\Http\Requests\API\V1\UnpublishHostEventRequest;

class HostEventController extends Controller
{
    public function index(): JsonResponse
    {
        $baseQuery = Event::query()
            ->controllableByUser(auth()->user())
            ->with(['user', 'eventType', 'managers', 'pendingManagerInvitations'])
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

    public function store(CreateEventRequest $request, EventType $eventType, #[CurrentUser] User $user): JsonResponse
    {
        $event = CreateApiEvent::run(
            host: $user,
            eventType: $eventType,
            data: EventCreationData::fromRequest($request, $eventType),
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
            'event' => $event->fresh(['managers', 'pendingManagerInvitations']),
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
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations'])),
        ]);
    }

    public function approve(ApproveHostEventRequest $request, Event $event): JsonResponse
    {
        ApproveEvent::run($event);

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations'])),
        ]);
    }
}
