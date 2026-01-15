<?php

namespace App\Http\Controllers\Attendee;

use App\Jobs\ExportContactsJob;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ExportContactsController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        ExportContactsJob::dispatch(
            user: auth()->user(),
            eventId: request('event'),
        );

        return back()->with(['notifications' => [
            'message' => 'Your contacts export is being generated. You will receive an email shortly.',
        ]]);
    }
}
