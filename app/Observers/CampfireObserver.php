<?php

namespace App\Observers;

use App\Jobs\SendNewCampfireSlackAlert;
use App\Models\Campfire;
use Illuminate\Support\Facades\Artisan;

class CampfireObserver
{
    public function created(Campfire $campfire): void
    {
        if (config('app.env') === 'production') {
            SendNewCampfireSlackAlert::dispatch($campfire);
        }

        Artisan::call('app:clear-campfire-cache');
    }

    public function updated(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }

    public function deleted(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }

    public function forceDeleted(Campfire $campfire): void
    {
        Artisan::call('app:clear-campfire-cache');
    }
}
