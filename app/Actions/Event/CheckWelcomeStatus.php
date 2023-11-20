<?php

namespace App\Actions\Event;

use App\Mail\Event\SendWelcomeEmail;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckWelcomeStatus
{
    use AsAction;

    public function handle(
        Event $event
    ): void {

        activity()
            ->performedOn($event)
            ->log('Check Welcome Status');

        if (! $event->welcomeHasBeenSent()) {

            Mail::to($event->user)->send(new SendWelcomeEmail($event));

            UpdateEvent::run([
                'welcome_sent' => true,
            ], $event);

            activity()
                ->performedOn($event)
                ->log('Send Welcome Email');
        }
    }
}
