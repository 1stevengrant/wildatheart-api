<?php

namespace App\Http\Controllers\Event;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Data\EventManagerInvitationWithEventData;

class AcceptHostInvitationController extends Controller
{
    /**
     * Show the confirmation page for accepting a host invitation
     */
    public function __invoke(Request $request, EventManagerInvitation $invitation)
    {
        $invitation->load('event', 'invitedByUser');

        // Check if this invitation is for the logged-in user
        if (auth()->user()->email !== $invitation->email) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => 'This invitation is for a different email address.',
                    'type' => 'error',
                ],
            ]);
        }

        if ($invitation->event === null) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => 'This event is no longer available.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if already accepted
        if (! $invitation->isPending()) {
            return to_route('my.events.edit', $invitation->event)->with([
                'notifications' => [
                    'message' => 'You are already a host for this event.',
                    'type' => 'info',
                ],
            ]);
        }

        return inertia('Invitations/AcceptHostInvitation', [
            'invitation' => EventManagerInvitationWithEventData::fromModel($invitation),
        ]);
    }
}
