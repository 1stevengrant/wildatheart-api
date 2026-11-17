<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Data\EventManagerData;
use App\Http\Controllers\Controller;
use App\Actions\Event\InviteEventManager;
use App\Actions\Event\RemoveEventManager;
use App\Exceptions\EventManagerException;
use Illuminate\Container\Attributes\CurrentUser;

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
    public function store(Request $request, Event $event, #[CurrentUser] User $user)
    {
        $this->authorize('manage', $event);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $result = InviteEventManager::run($event, $user, $request->string('email')->toString());
        } catch (EventManagerException $exception) {
            return back()->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }

        return back()->with([
            'notifications' => [
                'message' => $result->message(),
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Remove a host or cancel an invitation
     */
    public function destroy(Request $request, Event $event, string $manager, #[CurrentUser] User $user)
    {
        $this->authorize('manage', $event);

        try {
            $result = RemoveEventManager::run($event, $user, $manager);
        } catch (EventManagerException $exception) {
            return back()->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }

        return back()->with([
            'notifications' => [
                'message' => $result->message(),
                'type' => 'success',
            ],
        ]);
    }
}
