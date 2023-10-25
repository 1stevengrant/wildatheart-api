<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\Http\Request;

class ToggleConnectionContactedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Attendee $attendee)
    {

        if ($attendee->user_id !== auth()->id()) {
            abort(403);
        }

        if ($attendee->contacted_on) {

            $attendee->update([
                'contacted_on' => null,
            ]);

            activity()->performedOn($attendee)->causedBy(auth()->user())->log('Connection unmarked as not contacted');

            return redirect()->back()->with(['notifications' => [
                'message' => 'Connection updated',
                'type' => 'success',
            ],
            ]);
        }
        $attendee->update([
            'contacted_on' => now(),
        ]);

        activity()->performedOn($attendee)->causedBy(auth()->user())->log('Connection unmarked as contacted');

        return redirect()->back()->with(['notifications' => [
            'message' => 'Connection updated',
            'type' => 'success',
        ],
        ]);
    }
}
