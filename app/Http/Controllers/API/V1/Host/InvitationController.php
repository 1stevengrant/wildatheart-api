<?php

namespace App\Http\Controllers\API\V1\Host;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Actions\Event\AcceptCoHostInvitation;
use App\Data\EventManagerInvitationWithEventData;

class InvitationController extends Controller
{
    public function show(EventManagerInvitation $invitation): JsonResponse
    {
        if (auth()->user()->email !== $invitation->email) {
            return response()->json([
                'message' => 'This invitation is for a different email address.',
            ], 403);
        }

        $invitation->load('event', 'invitedByUser');

        return response()->json(EventManagerInvitationWithEventData::fromModel($invitation));
    }

    public function accept(EventManagerInvitation $invitation): JsonResponse
    {
        if (auth()->user()->email !== $invitation->email) {
            return response()->json([
                'message' => 'This invitation is for a different email address.',
            ], 403);
        }

        $eventId = $invitation->event_id;

        if ($invitation->isPending()) {
            AcceptCoHostInvitation::run($invitation, auth()->user());
        }

        return response()->json([
            'message' => 'You are now a host for this event!',
            'event_id' => $eventId,
        ]);
    }
}
