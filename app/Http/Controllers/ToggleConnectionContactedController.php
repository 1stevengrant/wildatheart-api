<?php

namespace App\Http\Controllers;

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

            return redirect()->back()->with(['notifications' => [
                'message' => 'Connection updated',
                'type' => 'success',
            ],
            ]);
        }
        $attendee->update([
            'contacted_on' => now(),
        ]);

        return redirect()->back()->with(['notifications' => [
            'message' => 'Connection updated',
            'type' => 'success',
        ],
        ]);
    }
}
