<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventManagerInvitation;
use App\Exceptions\EventManagerException;
use Illuminate\Container\Attributes\CurrentUser;
use App\Actions\Event\AcceptEventManagerInvitation;

class ConfirmHostInvitationController extends Controller
{
    /**
     * Process the host invitation acceptance
     */
    public function __invoke(Request $request, EventManagerInvitation $invitation, #[CurrentUser] User $user)
    {
        try {
            $event = AcceptEventManagerInvitation::run($invitation, $user);
        } catch (EventManagerException $exception) {
            return to_route('home')->with([
                'notifications' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }

        return to_route('my.events.edit', $event)->with([
            'notifications' => [
                'message' => "You are now a host for {$event->name}!",
                'type' => 'success',
            ],
        ]);
    }
}
