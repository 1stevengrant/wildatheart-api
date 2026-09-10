<?php

namespace App\Actions\ContactInvitation;

use App\Models\User;
use App\Models\ContactInvitation;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Auth\Access\AuthorizationException;

class CancelContactInvitation
{
    use AsAction;

    public function handle(User $host, ContactInvitation $invitation): void
    {
        if ($invitation->invited_by_user_id !== $host->id) {
            throw new AuthorizationException;
        }

        if ($invitation->isPending()) {
            $invitation->update(['active_key' => null, 'cancelled_at' => now()]);
        }

        if ($invitation->staged_image_path) {
            Storage::disk('s3')->delete($invitation->staged_image_path);
            $invitation->update(['staged_image_path' => null]);
        }
    }
}
