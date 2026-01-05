<?php

namespace App\Observers;

use App\Models\Attendee;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\AttendeeRegistered;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Mail\Event\AttendeeConnectedToHost;

class AttendeeObserver
{
    public function created(Attendee $attendee): void
    {
        // Notify primary host (CC managers)
        $mail = Mail::to($attendee->event->user->email);

        if ($attendee->event->managers->isNotEmpty()) {
            $mail->cc($attendee->event->managers->pluck('email')->toArray());
        }

        $mail->send(new AttendeeRegistered(
            event: $attendee->event,
            attendee: $attendee
        ));

        // Notify the attendee
        Mail::to($attendee->email)
            ->send(new AttendeeConnectedToHost(
                event: $attendee->event,
                attendee: $attendee
            ));

        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $attendee,
            ),
            new TagDonorJob(
                taggable: $attendee,
                weGiveTagId: config('services.wegive.attendee_tag_id'),
            ),
        ])->dispatch();
    }
}
