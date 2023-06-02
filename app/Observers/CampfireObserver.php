<?php

namespace App\Observers;

use App\Jobs\LookupLatitudeLongitude;
use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;

class CampfireObserver
{
    /**
     * Handle the Campfire "created" event.
     */
    public function created(Campfire $campfire): void
    {
        LookupLatitudeLongitude::dispatch($campfire);

        if (! $campfire->is_existing) {
            Mail::to($campfire->user)->send(new SendWelcomeEmail($campfire));
        }

        $campfire->update([
            'state_code' => getStateAbbreviation($campfire->state),
        ]);
    }

    /**
     * Handle the Campfire "updated" event.
     */
    public function updated(Campfire $campfire): void
    {
        //
    }

    /**
     * Handle the Campfire "deleted" event.
     */
    public function deleted(Campfire $campfire): void
    {
        //
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
        //
    }
}
