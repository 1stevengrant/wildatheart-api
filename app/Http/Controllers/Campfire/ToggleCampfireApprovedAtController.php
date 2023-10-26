<?php

namespace App\Http\Controllers\Campfire;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class ToggleCampfireApprovedAtController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->user_id != auth()->user()->id) {
            abort(403);
        }

        $campfire->update([
            'approved_at' => $campfire->approved_at ? null : now(),
        ]);

        activity()
            ->performedOn($campfire)
            ->causedBy(auth()->user())
            ->log('Toggle Campfire Approved At');

        return to_route('my.campfires')->with(['notifications' => [
            'message' => 'Fire updated successfully!',
            'type' => 'success',
        ]]);
    }
}
