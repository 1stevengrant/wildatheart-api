<?php

namespace App\Actions\Campfire;

use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckWelcomeStatus
{
    use AsAction;

    public function handle(
        Campfire $campfire
    ): void {
        if (! $campfire->welcomeHasBeenSent()) {
            Mail::to($campfire->user)->send(new SendWelcomeEmail($campfire));

            $campfire->update([
                'welcome_sent' => true,
            ]);
        }
    }
}
