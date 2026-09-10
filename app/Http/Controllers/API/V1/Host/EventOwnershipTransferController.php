<?php

namespace App\Http\Controllers\API\V1\Host;

use App\Models\User;
use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Mail\OwnershipTransferInvitationMail;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\API\V1\DestroyOwnershipTransferRequest;
use App\Http\Requests\API\V1\InitiateOwnershipTransferRequest;

class EventOwnershipTransferController extends Controller
{
    public function store(InitiateOwnershipTransferRequest $request, Event $event): JsonResponse
    {
        $newOwner = User::findOrFail($request->integer('user_id'));

        if ($newOwner->id === $event->user_id) {
            throw ValidationException::withMessages([
                'user_id' => 'You already own this event.',
            ]);
        }

        if (! $event->managers()->where('user_id', $newOwner->id)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'Only existing hosts can be promoted to owner. Please add them as a host first.',
            ]);
        }

        if ($event->pendingOwnershipTransfer) {
            throw ValidationException::withMessages([
                'user_id' => 'There is already a pending ownership transfer for this event.',
            ]);
        }

        $transfer = EventOwnershipTransfer::create([
            'event_id' => $event->id,
            'email' => $newOwner->email,
            'transferred_by_user_id' => auth()->id(),
        ]);

        Mail::to($newOwner->email)->send(
            new OwnershipTransferInvitationMail($event, auth()->user()->fullname, $transfer, true)
        );

        return response()->json([
            'success' => true,
            'event' => EventData::fromModel(
                $event->fresh(['user', 'eventType', 'managers', 'pendingManagerInvitations', 'pendingOwnershipTransfer.transferredByUser'])
            ),
        ]);
    }

    public function destroy(DestroyOwnershipTransferRequest $request, Event $event): JsonResponse
    {
        $transfer = $event->pendingOwnershipTransfer;

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
