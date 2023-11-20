<?php

namespace App\Observers;

use App\Jobs\SendNewEventSlackAlert;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;

class EventObserver
{
    public function created(Event $event): void
    {
        if (config('app.env') === 'production') {
            SendNewEventSlackAlert::dispatch($event);
        }

        Artisan::call('app:clear-event-cache');
    }

    public function updated(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
    }

    public function deleted(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
    }

    public function forceDeleted(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
    }
}
