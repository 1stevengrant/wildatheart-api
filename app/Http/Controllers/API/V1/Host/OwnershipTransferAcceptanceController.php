<?php

namespace App\Http\Controllers\API\V1\Host;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\EventOwnershipTransfer;
use App\Actions\Event\TransferEventOwnership;

class OwnershipTransferAcceptanceController extends Controller
{
    public function store(EventOwnershipTransfer $transfer): JsonResponse
    {
        abort_unless(auth()->user()->email === $transfer->email, 403, 'This ownership transfer is for a different email address.');

        $eventId = $transfer->event_id;

        if ($transfer->isPending()) {
            TransferEventOwnership::run($transfer, auth()->user());
        }

        return response()->json(['message' => 'You are now the owner of this event!', 'event_id' => $eventId]);
    }
}
