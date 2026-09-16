<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Data\EventOwnershipTransferData;
use Illuminate\Validation\ValidationException;
use App\Actions\Event\CancelEventOwnershipTransfer;
use App\Actions\Event\InitiateEventOwnershipTransfer;

class EventOwnershipTransferController extends Controller
{
    /**
     * Get pending ownership transfer for an event
     */
    public function show(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $pendingTransfer = $event->pendingOwnershipTransfer?->load('transferredByUser');

        return response()->json([
            'transfer' => $pendingTransfer ? EventOwnershipTransferData::fromModel($pendingTransfer) : null,
        ]);
    }

    /**
     * Initiate an ownership transfer
     */
    public function store(Request $request, Event $event)
    {
        $this->authorize('transferOwnership', $event);

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

        try {
            InitiateEventOwnershipTransfer::run($event, $user, auth()->user());
        } catch (ValidationException $exception) {
            return back()->with([
                'notifications' => [
                    'message' => $exception->validator->errors()->first(),
                    'type' => 'error',
                ],
            ]);
        }

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
        $this->authorize('transferOwnership', $event);
        $transfer = CancelEventOwnershipTransfer::run($event, auth()->user());

        if (! $transfer) {
            return back()->with([
                'notifications' => [
                    'message' => 'No pending ownership transfer found.',
                    'type' => 'error',
                ],
            ]);
        }

        return back()->with([
            'notifications' => [
                'message' => "Ownership transfer to {$transfer->email} has been cancelled.",
                'type' => 'success',
            ],
        ]);
    }
}
