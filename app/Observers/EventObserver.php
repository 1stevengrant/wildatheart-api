<?php

namespace App\Observers;

use App\Models\Event;
use App\Jobs\User\TagWeGiveDonorJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\Event\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;

class EventObserver
{
    public function created(Event $event): void
    {
        SendNewEventSlackAlert::dispatch($event);

        Mail::to($event->user)->send(new NewEventCreatedMailable($event));

        Artisan::call('app:clear-event-cache');

        $event->update([
            'sex' => determineSexOfEvent($event->eventType),
        ]);

        TagWeGiveDonorJob::dispatch($event);
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
