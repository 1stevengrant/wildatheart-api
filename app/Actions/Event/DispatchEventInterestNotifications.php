<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\UserConnectedToHost;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\Event\EventInterestRegistered;
use App\Jobs\Admin\PostUserInterestToSlackJob;

class DispatchEventInterestNotifications
{
    use AsAction;

    public function handle(Event $event, User $user): void
    {
        $hostMail = Mail::to($event->user->email);

        if ($event->managers->isNotEmpty()) {
            $hostMail->cc($event->managers->pluck('email')->toArray());
        }

        $hostMail->send(new EventInterestRegistered(
            event: $event,
            user: $user
        ));

        Mail::to($user->email)
            ->send(new UserConnectedToHost(
                event: $event,
                user: $user
            ));

        PostUserInterestToSlackJob::dispatch($user, $event);

        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $user,
            ),
            new TagDonorJob(
                taggable: $user,
                weGiveTagId: config('services.wegive.attendee_tag_id'),
            ),
        ])->dispatch();
    }
}
