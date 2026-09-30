<?php

namespace App\Http\Controllers\API\V1\Host;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\EventOwnershipTransfer;
use App\Data\EventOwnershipTransferWithEventData;

class OwnershipTransferController extends Controller
{
    public function show(EventOwnershipTransfer $transfer): JsonResponse
    {
        if (auth()->user()->email !== $transfer->email) {
            return response()->json([
                'message' => 'This ownership transfer is for a different email address.',
            ], 403);
        }

        $transfer->load('event', 'transferredByUser');

        return response()->json(EventOwnershipTransferWithEventData::fromModel($transfer));
    }
}
