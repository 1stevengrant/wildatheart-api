<?php

namespace App\Observers;

use App\Jobs\SendNewCampfireSlackAlert;
use App\Models\Campfire;
use Illuminate\Support\Facades\Artisan;

class CampfireObserver
{
    /**
     * Handle the Campfire "created" event.
     */
    public function created(Campfire $campfire): void
    {
        $campfire->update([
            'state_code' => getStateAbbreviation($campfire->state),
        ]);

        if (config('app.env') === 'production') {
            SendNewCampfireSlackAlert::dispatch($campfire);
        }

        Artisan::call('app:clear-campfire-cache');
    }

    /**
     * Handle the Campfire "updated" event.
     */
    public function updated(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }

    /**
     * Handle the Campfire "deleted" event.
     */
    public function deleted(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }

    /**
     * Handle the Campfire "restored" event.
     */
    public function restored(Campfire $campfire): void
    {
        //
    }

    /**
     * Handle the Campfire "force deleted" event.
     */
    public function forceDeleted(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }
}
