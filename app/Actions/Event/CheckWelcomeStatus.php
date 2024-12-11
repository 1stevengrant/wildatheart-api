<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Mail\Event\SendWelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckWelcomeStatus
{
    use AsAction;

    public function handle(
        Event $event
    ): void {

        dispatch(new LogActivityJob(
            description: 'Check Welcome Status',
            performedOn: $event,
        ));

        if (! $event->welcomeHasBeenSent()) {

            Mail::to($event->user)->send(new SendWelcomeEmail($event));

            UpdateEvent::run([
                'welcome_sent' => true,
            ], $event);

            dispatch(new LogActivityJob(
                description: 'Send Welcome Email',
                performedOn: $event,
            ));
        }
    }
}
