<?php

namespace App\Http\Controllers\User;

use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DestroyUserImageController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->deleteProfilePhoto();

        LogActivityJob::dispatch(
            description: 'Profile photo deleted',
            causedBy: $request->user(),
            performedOn: $request->user(),
        );

        return back(303)->with(['notifications' => [
            'message' => 'Profile photo deleted!',
            'type' => 'success',
        ],
        ]);
    }
}
