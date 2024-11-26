<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;

class DestroyUserImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->user()->deleteProfilePhoto();

        dispatch(new LogActivityJob(
            description: 'Profile photo deleted',
            causedBy: $request->user(),
            performedOn: $request->user(),
        ));

        return back(303)->with(['notifications' => [
            'message' => 'Profile photo deleted!',
            'type' => 'success',
        ],
        ]);
    }
}
