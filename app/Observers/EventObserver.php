<?php

namespace App\Observers;

use App\Jobs\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class EventObserver
{
    public function created(Event $event): void
    {
        if (app()->isProduction()) {
//            SendNewEventSlackAlert::dispatch($event);
        }

        Mail::to($event->user)->send(new NewEventCreatedMailable($event));

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
