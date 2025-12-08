<?php

namespace App\Http\Controllers\Event;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventOwnershipTransfer;
use App\Data\EventOwnershipTransferWithEventData;

class AcceptOwnershipTransferController extends Controller
{
    /**
     * Show the confirmation page for accepting an ownership transfer
     */
    public function __invoke(Request $request, EventOwnershipTransfer $transfer)
    {
        // Check if this transfer is for the logged-in user
        if (auth()->user()->email !== $transfer->email) {
            return redirect()->route('home')->with([
                'notifications' => [
                    'message' => 'This ownership transfer is for a different email address.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if already accepted
        if (! $transfer->isPending()) {
            return redirect()->route('my.events.edit', $transfer->event)->with([
                'notifications' => [
                    'message' => 'You are already the owner of this event.',
                    'type' => 'info',
                ],
            ]);
        }

        return inertia('Invitations/AcceptOwnershipTransfer', [
            'transfer' => EventOwnershipTransferWithEventData::fromModel($transfer),
        ]);
    }
}
