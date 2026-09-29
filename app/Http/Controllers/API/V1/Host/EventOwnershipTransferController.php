<?php

namespace App\Http\Controllers\API\V1\Host;

use App\Models\User;
use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Data\V1\InitiateOwnershipTransferData;
use App\Mail\V1\OwnershipTransferInvitationMail;
use App\Actions\Event\CancelEventOwnershipTransfer;
use App\Actions\Event\InitiateEventOwnershipTransfer;

class EventOwnershipTransferController extends Controller
{
    public function store(InitiateOwnershipTransferData $data, Event $event): JsonResponse
    {
        $this->authorize('transferOwnership', $event);
        $newOwner = User::findOrFail($data->user_id);
        $transfer = InitiateEventOwnershipTransfer::run($event, $newOwner, auth()->user());

        Mail::to($newOwner->email)->send(
            new OwnershipTransferInvitationMail($event->fresh(), auth()->user()->fullname, $transfer)
        );

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel(
                $event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])
            ),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('transferOwnership', $event);
        $transfer = CancelEventOwnershipTransfer::run($event, auth()->user());

        if (! $transfer) {
            return response()->json([
                'message' => 'No pending ownership transfer found.',
            ], 404);
        }

        $transfer->delete();

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel(
                $event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])
            ),
        ]);
    }
}
