<?php

namespace App\Actions\Event;

use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class EnsureHostInviteToken
{
    use AsAction;

    /**
     * Return the event's shareable join-as-host URL, generating its token on first use.
     */
    public function handle(Event $event): string
    {
        if (blank($event->host_invite_token)) {
            do {
                $token = mb_rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            } while (Event::query()->where('host_invite_token', $token)->exists());

            $event->forceFill(['host_invite_token' => $token])->saveQuietly();
        }

        return mb_rtrim((string) config('app.frontend_url'), '/') . '/join-as-host/' . $event->host_invite_token;
    }
}
