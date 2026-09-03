<?php

namespace App\Actions\ContactInvitation;

use App\Models\ContactInvitation;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class CleanupExpiredContactInvitations
{
    use AsAction;

    public function handle(): int
    {
        $count = 0;

        ContactInvitation::query()
            ->where('expires_at', '<=', now())
            ->whereNotNull('active_key')
            ->chunkById(100, function ($invitations) use (&$count): void {
                foreach ($invitations as $invitation) {
                    if ($invitation->staged_image_path) {
                        Storage::disk('s3')->delete($invitation->staged_image_path);
                    }

                    $invitation->update(['active_key' => null, 'staged_image_path' => null]);
                    $count++;
                }
            });

        return $count;
    }
}
