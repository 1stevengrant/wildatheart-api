<?php

namespace App\Observers;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use App\Features\BetaFeature;
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

    public function updated(User $user): void
    {
        if ($user->wasChanged('beta_enabled')) {
            $this->forgetBetaFeatureCache($user);
        }
    }

    public function deleting(User $user): void
    {
        $user->events->each->delete();
        $user->addresses()->delete();
    }

    public function deleted(User $user): void
    {
        $this->forgetBetaFeatureCache($user);
    }

    public function restored(User $user): void
    {
        //
    }

    public function forceDeleted(User $user): void
    {
        //
    }

    private function forgetBetaFeatureCache(User $user): void
    {
        try {
            Feature::for($user)->forget(BetaFeature::class);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
