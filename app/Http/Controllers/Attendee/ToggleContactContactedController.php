<?php

namespace App\Http\Controllers\Attendee;

use App\Models\Attendee;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ToggleContactContactedController extends Controller
{
    public function __invoke(Request $request, Attendee $attendee)
    {
        abort_unless($attendee->user_id == auth()->user()->id, 403);

        $wasContacted = (bool) $attendee->contacted_on;

        $attendee->update([
            'contacted_on' => $wasContacted ? null : now(),
        ]);

        LogActivityJob::dispatch(
            description: $wasContacted ? 'Contact unmarked as not contacted' : 'Contact marked as contacted',
            causedBy: auth()->user(),
            performedOn: $attendee,
        );

        return redirect()->back()->with(['notifications' => [
            'message' => 'Contact updated',
            'type' => 'success',
        ]]);
    }
}
