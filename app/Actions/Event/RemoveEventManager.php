<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\HostRemovedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoveEventManager
{
    use AsAction;

    public function handle(Event $event, string $managerId, User $removedBy): void
    {
        $invitation = EventManagerInvitation::withoutGlobalScopes()
            ->where('event_id', $event->id)
            ->where('id', $managerId)
            ->whereNull('accepted_at')
            ->first();

        if ($invitation) {
            $invitation->delete();

            return;
        }

        $user = User::query()->find($managerId);

        if (! $user) {
            throw new NotFoundHttpException('Host or invitation not found.');
        }

        if ($user->id === $event->user_id) {
            throw ValidationException::withMessages([
                'manager' => 'Cannot remove the event owner.',
            ]);
        }

        if (! $event->managers()->where('user_id', $user->id)->exists()) {
            throw new NotFoundHttpException('This user is not a host for this event.');
        }

        $event->managers()->detach($user->id);

        Mail::to($user->email)->send(new HostRemovedMail($event, $user, $removedBy));

        if ($removedBy->id !== $event->user_id) {
            Mail::to($event->user->email)->send(new HostRemovedMail($event, $user, $removedBy));
        }
    }
}
