<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Mail\HostAddedMail;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptCoHostInvitation
{
    use AsAction;

    public function handle(EventManagerInvitation $invitation, User $newHost): void
    {
        $event = $invitation->event;
        $eventOwner = $event->user;

        $event->managers()->attach($newHost->id);

        $invitation->markAsAccepted();

        Mail::to($newHost->email)->send(new HostAddedMail($event, $newHost, $eventOwner));

        Mail::to($eventOwner->email)->send(new HostAddedMail($event, $newHost, $eventOwner));

        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $newHost,
            ),
            new TagDonorJob(
                taggable: $newHost,
                weGiveTagId: $event->eventType->wegive_tag,
            ),
        ])->dispatch();
    }
}
