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
        // Notify primary host
        Mail::to($attendee->event->user->email)
            ->send(new AttendeeRegistered(
                event: $attendee->event,
                attendee: $attendee
            ));

        // Notify all event managers
        foreach ($attendee->event->managers as $manager) {
            Mail::to($manager->email)
                ->send(new AttendeeRegistered(
                    event: $attendee->event,
                    attendee: $attendee
                ));
        }

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
