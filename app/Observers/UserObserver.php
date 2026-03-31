<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Jobs\Admin\CheckIfUserShouldBeAdmin;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->uuid = (string) Str::uuid();
    }

    public function created(User $user): void
    {
        CheckIfUserShouldBeAdmin::dispatch($user);

        FindOrCreateDonorJob::dispatch(
            donorModel: $user,
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        $user->events()->delete();
        $user->addresses()->delete();
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
