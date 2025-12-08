<?php

namespace App\Observers;

use App\Models\User;
use App\Jobs\WeGive\FindOrCreateDonorJob;

class UserObserver
{
    public function created(User $user): void
    {
        FindOrCreateDonorJob::dispatch(
            donorModel: $user,
        );

        // Note: Users must now explicitly accept host invitations and ownership transfers
        // No automatic activation occurs when an account is created
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
