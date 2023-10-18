<?php

namespace App\Observers;

use App\Mail\Campfire\AttendeeConnectedToHost;
use App\Mail\Campfire\AttendeeRegistered;
use App\Models\Attendee;
use Illuminate\Support\Facades\Mail;

class AttendeeObserver
{
    public function created(Attendee $attendee): void
    {
        Mail::to($attendee->campfire->host->email)
            ->send(new AttendeeRegistered(
                campfire: $attendee->campfire,
                attendee: $attendee
            ));

        Mail::to($attendee->email)
            ->send(new AttendeeConnectedToHost(
                campfire: $attendee->campfire,
                attendee: $attendee
            ));
    }
}
