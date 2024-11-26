<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Jobs\LogActivityJob;
use App\Models\Attendee;
use Illuminate\Http\Request;

class ToggleContactContactedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Attendee $attendee)
    {
        abort_unless($attendee->user_id == auth()->user()->id, 403);

        if ($attendee->contacted_on) {

            $attendee->update([
                'contacted_on' => null,
            ]);

            dispatch(new LogActivityJob(
                description: 'Contact unmarked as not contacted',
                causedBy: auth()->user(),
                performedOn: $attendee,
            ));

            return redirect()->back()->with(['notifications' => [
                'message' => 'Contact updated',
                'type' => 'success',
            ],
            ]);
        }
        $attendee->update([
            'contacted_on' => now(),
        ]);

        dispatch(new LogActivityJob(
            description: 'Contact marked as contacted',
            causedBy: auth()->user(),
            performedOn: $attendee,
        ));

        return redirect()->back()->with(['notifications' => [
            'message' => 'Contact updated',
            'type' => 'success',
        ],
        ]);
    }
}
