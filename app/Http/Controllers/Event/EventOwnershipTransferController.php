<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Data\EventOwnershipTransferData;
use Illuminate\Container\Attributes\CurrentUser;
use App\Actions\Event\CancelEventOwnershipTransfer;
use App\Exceptions\EventOwnershipTransferException;
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
    public function store(Request $request, Event $event, #[CurrentUser] User $user)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $result = InitiateEventOwnershipTransfer::run($event, $user, $request->string('email')->toString());
        } catch (EventOwnershipTransferException $exception) {
            return back()->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }

        return back()->with([
            'notifications' => [
                'message' => "Ownership transfer invitation has been sent to {$result->recipient->fullname}.",
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Cancel a pending ownership transfer
     */
    public function destroy(Request $request, Event $event, #[CurrentUser] User $user)
    {
        try {
            $transfer = CancelEventOwnershipTransfer::run($event, $user);
        } catch (EventOwnershipTransferException $exception) {
            return back()->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
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
