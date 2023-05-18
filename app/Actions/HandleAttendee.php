<?php

namespace App\Actions;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleAttendee
{
    use AsAction;

    public function handle(
        string $campfireId,
        string $currentUrl
    ) {
        ray($currentUrl);
        session()->put('userType', User::ROLE_ATTENDEE);
        session()->put('currentUrl', $currentUrl);
        session()->put('campfireId', $campfireId);

        return redirect()->route('social.login', ['provider' => 'auth0']);
    }
}
