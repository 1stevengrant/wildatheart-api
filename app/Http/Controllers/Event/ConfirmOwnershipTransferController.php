<?php

namespace App\Http\Controllers\Event;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventOwnershipTransfer;
use App\Actions\Event\TransferEventOwnership;

class ConfirmOwnershipTransferController extends Controller
{
    /**
     * Process the ownership transfer acceptance
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

        // Store event reference before transfer (transfer will be deleted)
        $event = $transfer->event;

        // Transfer ownership
        if ($transfer->isPending()) {
            TransferEventOwnership::run($transfer, auth()->user());
        }

        // Redirect to the event (use stored $event since transfer is deleted)
        return redirect()->route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now the owner of {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
