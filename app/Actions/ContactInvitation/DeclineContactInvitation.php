<?php

namespace App\Actions\ContactInvitation;

use App\Models\ContactInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class DeclineContactInvitation
{
    use AsAction;

    public function handle(string $token): string
    {
        $invitation = DB::transaction(function () use ($token): ContactInvitation {
            $invitation = ContactInvitation::query()->where('token_hash', ContactInvitation::tokenHash($token))->lockForUpdate()->firstOrFail();

            if ($invitation->accepted_at) {
                abort(409, 'Invitation already accepted.');
            }

            if ($invitation->cancelled_at || $invitation->expires_at->isPast()) {
                abort(410, 'Invitation is no longer available.');
            }

            if (! $invitation->declined_at) {
                $invitation->update(['active_key' => null, 'declined_at' => now()]);
            }

            return $invitation;
        });

        if ($invitation->staged_image_path) {
            Storage::disk('s3')->delete($invitation->staged_image_path);
            $invitation->update(['staged_image_path' => null]);
        }

        return 'declined';
    }
}
