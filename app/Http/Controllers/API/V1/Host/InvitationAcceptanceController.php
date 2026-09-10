<?php

namespace App\Http\Controllers\API\V1\Host;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Actions\Event\AcceptCoHostInvitation;

class InvitationAcceptanceController extends Controller
{
    public function store(EventManagerInvitation $invitation): JsonResponse
    {
        abort_unless(auth()->user()->email === $invitation->email, 403, 'This invitation is for a different email address.');

        $eventId = $invitation->event_id;

        if ($invitation->isPending()) {
            AcceptCoHostInvitation::run($invitation, auth()->user());
        }

        return response()->json(['message' => 'You are now a host for this event!', 'event_id' => $eventId]);
    }
}
