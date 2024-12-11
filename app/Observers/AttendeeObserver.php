<?php

namespace App\Observers;

use App\Models\Attendee;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\AttendeeRegistered;
use App\Mail\Event\AttendeeConnectedToHost;

class AttendeeObserver
{
    public function created(Attendee $attendee): void
    {
        Mail::to($attendee->event->host->email)
            ->send(new AttendeeRegistered(
                event: $attendee->event,
                attendee: $attendee
            ));

        Mail::to($attendee->email)
            ->send(new AttendeeConnectedToHost(
                event: $attendee->event,
                attendee: $attendee
            ));
    }
}
