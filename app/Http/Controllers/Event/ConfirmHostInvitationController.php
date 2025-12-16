<?php

namespace App\Http\Controllers\Event;

use App\Mail\HostAddedMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

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

        // Activate the invitation
        if ($invitation->isPending()) {
            $eventOwner = $event->user;

            $invitation->event->managers()->attach(auth()->id());

            $invitation->markAsAccepted();

            // Send notification to the new host
            Mail::to(auth()->user()->email)->send(new HostAddedMail($event, auth()->user(), $eventOwner));

            // Send notification to the event owner
            Mail::to($eventOwner->email)->send(new HostAddedMail($event, auth()->user(), $eventOwner));
        }

        return to_route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now a host for {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
