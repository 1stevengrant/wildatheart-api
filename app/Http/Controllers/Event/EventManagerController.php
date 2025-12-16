<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Mail\HostRemovedMail;
use App\Data\EventManagerData;
use App\Mail\HostInvitationMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

class EventManagerController extends Controller
{
    /**
     * Get all hosts for an event
     */
    public function index(Request $request, Event $event)
    {
        $this->authorize('manage', $event);

        $existingManagers = $event->managers->map(fn ($manager) => EventManagerData::fromUser($manager));
        $pendingInvitations = $event->pendingManagerInvitations->map(fn ($invitation) => EventManagerData::fromInvitation($invitation));
        $allManagers = $existingManagers->merge($pendingInvitations);

        return response()->json([
            'managers' => $allManagers,
        ]);
    }

    /**
     * Invite a user to be a host
     */
    public function store(Request $request, Event $event)
    {
        $this->authorize('manage', $event);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(mb_trim($request->email));
        $user = User::where('email', $email)->first();

        // Check if user is the event owner
        if ($user && $user->id === $event->user_id) {
            return back()->with([
                'notifications' => [
                    'message' => 'This user is already the event owner.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if user is already a host
        if ($user && $event->managers()->where('user_id', $user->id)->exists()) {
            return back()->with([
                'notifications' => [
                    'message' => 'This user is already a host for this event.',
                    'type' => 'error',
                ],
            ]);
        }

        // Check if there's already a pending invitation
        if ($event->pendingManagerInvitations()->where('email', $email)->exists()) {
            return back()->with([
                'notifications' => [
                    'message' => 'An invitation has already been sent to this email address.',
                    'type' => 'error',
                ],
            ]);
        }

        // Create a pending invitation (for both existing and new users)
        $invitation = EventManagerInvitation::create([
            'event_id' => $event->id,
            'email' => $email,
            'invited_by_user_id' => auth()->id(),
        ]);

        // Send invitation email
        Mail::to($email)->send(new HostInvitationMail($event, auth()->user()->fullname, $invitation));

        $message = $user
            ? "An invitation has been sent to {$user->fullname}. They will become a host once they accept."
            : "An invitation has been sent to $email. They will become a host once they create an account and accept.";

        return back()->with([
            'notifications' => [
                'message' => $message,
                'type' => 'success',
            ],
        ]);

    }

    /**
     * Remove a host or cancel an invitation
     */
    public function destroy(Request $request, Event $event, $managerId)
    {
        $this->authorize('manage', $event);

        // Check for a pending invitation first (more specific query with event_id)
        $invitation = EventManagerInvitation::where('event_id', $event->id)
            ->where('id', $managerId)
            ->first();

        if ($invitation) {
            $invitation->delete();

            return back()->with([
                'notifications' => [
                    'message' => "Invitation to $invitation->email has been cancelled.",
                    'type' => 'success',
                ],
            ]);
        }

        // Try to find as a user (existing host)
        $user = User::find($managerId);

        if ($user) {
            // Prevent removing the event owner
            if ($user->id === $event->user_id) {
                return back()->with([
                    'notifications' => [
                        'message' => 'Cannot remove the event owner.',
                        'type' => 'error',
                    ],
                ]);
            }

            // Check if user is actually a host
            if (! $event->managers()->where('user_id', $user->id)->exists()) {
                return back()->with([
                    'notifications' => [
                        'message' => 'This user is not a host for this event.',
                        'type' => 'error',
                    ],
                ]);
            }

            $event->managers()->detach($user->id);

            // Send notification to the removed host
            Mail::to($user->email)->send(new HostRemovedMail($event, $user, auth()->user()));

            // Send notification to the event owner (if not the one who removed)
            if (auth()->id() !== $event->user_id) {
                Mail::to($event->user->email)->send(new HostRemovedMail($event, $user, auth()->user()));
            }

            return back()->with([
                'notifications' => [
                    'message' => "$user->fullname has been removed as a host.",
                    'type' => 'success',
                ],
            ]);
        }

        // Neither invitation nor user found
        return back()->with([
            'notifications' => [
                'message' => 'Host or invitation not found.',
                'type' => 'error',
            ],
        ]);
    }
}
