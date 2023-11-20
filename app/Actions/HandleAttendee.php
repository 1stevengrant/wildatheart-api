<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleAttendee
{
    use AsAction;

    public function handle(
        string $eventId,
        string $currentUrl
    ): RedirectResponse {

        session()->put('userType', User::ROLE_ATTENDEE);
        session()->put('currentUrl', $currentUrl);
        session()->put('eventId', $eventId);

        return redirect()->route('login');
    }
}
