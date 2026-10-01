<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Event\RemoveEventManager;
use App\Http\Requests\API\V1\DestroyHostEventManagerRequest;

class HostEventManagerController extends Controller
{
    public function destroy(DestroyHostEventManagerRequest $request, Event $event, string $manager): JsonResponse
    {
        RemoveEventManager::run($event, $manager, $request->user());

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel($event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])),
        ]);
    }
}
