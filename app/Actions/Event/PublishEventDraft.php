<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Support\EventDateHelper;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\API\V1\PublishEventRequest;

class PublishEventDraft
{
    use AsAction;

    public function handle(
        PublishEventRequest $request,
        EventType $eventType,
        Event $event,
        User $host,
    ): Event {
        EventDateHelper::adjustEndDateForRetreat(
            request: $request,
            groupType: $eventType->group_type,
            eventTypeName: $eventType->name,
        );

        $event = SaveEventDraft::run($request, $eventType, $host);

        ApproveEvent::run($event);

        SendMaterialsOrderToZapier::run(
            event: $event,
            eventType: $eventType,
            user: $host,
            quantity: $request->input('number_of_items_in_material_order'),
        );

        SendPendingCoHostInvitations::run($event->fresh(['pendingManagerInvitations']), $host);

        return $event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser']);
    }
}
