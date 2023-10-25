<?php

namespace App\Http\Controllers\Campfire;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class DeleteCampfireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->user_id != auth()->user()->id) {
            return redirect()->back()->with(['notifications' => [
                'message' => 'You are not authorized to delete this fire.',
                'type' => 'error',
            ],
            ]);
        }

        $campfire->delete();

        activity()->causedBy(auth()->user())
            ->performedOn($campfire)
            ->log('Soft Delete Campfire');

        return redirect()->back()->with(['notifications' => [
            'message' => 'Fire deleted successfully.',
            'type' => 'success',
        ],
        ]);
    }
}
