<?php

namespace App\Http\Controllers\Event;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Actions\Event\AcceptCoHostInvitation;

class ConfirmHostInvitationController extends Controller
{
    /**
     * Process the host invitation acceptance
     */
    public function __invoke(Request $request, EventManagerInvitation $invitation)
    {
        // Check if this invitation is for the logged-in user
        if (auth()->user()->email !== $invitation->email) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => 'This invitation is for a different email address.',
                    'type' => 'error',
                ],
            ]);
        }

        $event = $invitation->event;

        if ($event === null) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => 'This event is no longer available.',
                    'type' => 'error',
                ],
            ]);
        }

        if ($invitation->isPending()) {
            AcceptCoHostInvitation::run($invitation, auth()->user());
        }

        return to_route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now a host for {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
