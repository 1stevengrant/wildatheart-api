<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Data\EventOwnershipTransferData;
use App\Mail\OwnershipTransferInvitationMail;

class EventOwnershipTransferController extends Controller
{
    /**
     * Get pending ownership transfer for an event
     */
    public function show(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $pendingTransfer = $event->pendingOwnershipTransfer;

        return response()->json([
            'transfer' => $pendingTransfer ? EventOwnershipTransferData::fromModel($pendingTransfer) : null,
        ]);
    }

    /**
     * Initiate an ownership transfer
     */
    public function store(Request $request, Event $event)
    {
        // Only the current owner can transfer ownership
        if (auth()->id() !== $event->user_id) {
            return back()->with([
                'notifications' => [
                    'message' => 'Only the event owner can transfer ownership.',
                    'type' => 'error',
                ],
            ]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(mb_trim($request->email));
        $user = User::where('email', $email)->first();

        // Check if user exists
        if (! $user) {
            return back()->with([
                'notifications' => [
                    'message' => 'User not found. Only existing hosts can be promoted to owner.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if user is trying to transfer to themselves
        if ($user->id === $event->user_id) {
            return back()->with([
                'notifications' => [
                    'message' => 'You already own this event.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if user is a host of this event
        if (! $event->managers()->where('user_id', $user->id)->exists()) {
            return back()->with([
                'notifications' => [
                    'message' => 'Only existing hosts can be promoted to owner. Please add them as a host first.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if there's already a pending transfer
        if ($event->pendingOwnershipTransfer) {
            return back()->with([
                'notifications' => [
                    'message' => 'There is already a pending ownership transfer for this event.',
                    'type' => 'error',
                ],
            ]);
        }

        // Create the transfer request
        $transfer = EventOwnershipTransfer::create([
            'event_id' => $event->id,
            'email' => $email,
            'transferred_by_name' => auth()->user()->fullname,
        ]);

        // Send transfer notification email
        Mail::to($email)->send(new OwnershipTransferInvitationMail($event, auth()->user()->fullname, $transfer, true));

        return back()->with([
            'notifications' => [
                'message' => "Ownership transfer invitation has been sent to {$user->fullname}.",
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Cancel a pending ownership transfer
     */
    public function destroy(Request $request, Event $event)
    {
        // Only the current owner can cancel a transfer
        if (auth()->id() !== $event->user_id) {
            return back()->with([
                'notifications' => [
                    'message' => 'Only the event owner can cancel an ownership transfer.',
                    'type' => 'error',
                ],
            ]);
        }

        $transfer = $event->pendingOwnershipTransfer;

        if (! $transfer) {
            return back()->with([
                'notifications' => [
                    'message' => 'No pending ownership transfer found.',
                    'type' => 'error',
                ],
            ]);
        }

        $transfer->delete();

        return back()->with([
            'notifications' => [
                'message' => "Ownership transfer to {$transfer->email} has been cancelled.",
                'type' => 'success',
            ],
        ]);
    }
}
