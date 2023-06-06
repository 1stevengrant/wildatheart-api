<?php

namespace App\Observers;

use App\Mail\Campfire\AttendeeRegistered;
use App\Mail\Fires\AttendeeConnectedToHost;
use App\Models\Attendee;
use Illuminate\Support\Facades\Mail;

class AttendeeObserver
{
    /**
     * Handle the Attendee "created" event.
     */
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

    /**
     * Handle the Attendee "updated" event.
     */
    public function updated(Attendee $attendee): void
    {
        //
    }

    /**
     * Handle the Attendee "deleted" event.
     */
    public function deleted(Attendee $attendee): void
    {
        //
    }

    /**
     * Handle the Attendee "restored" event.
     */
    public function restored(Attendee $attendee): void
    {
        //
    }

    /**
     * Handle the Attendee "force deleted" event.
     */
    public function forceDeleted(Attendee $attendee): void
    {
        //
    }
}
