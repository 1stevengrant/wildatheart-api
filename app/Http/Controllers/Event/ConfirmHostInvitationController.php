<?php

namespace App\Http\Controllers\Event;

use App\Mail\HostAddedMail;
use Illuminate\Http\Request;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Jobs\WeGive\FindOrCreateDonorJob;

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

            // Tag the new co-host in WeGive
            Bus::chain([
                new FindOrCreateDonorJob(
                    donorModel: auth()->user(),
                ),
                new TagDonorJob(
                    taggable: auth()->user(),
                    weGiveTagId: $event->eventType->wegive_tag,
                ),
            ])->dispatch();
        }

        return to_route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now a host for {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
