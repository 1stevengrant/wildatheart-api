<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DestroyUserImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->user()->deleteProfilePhoto();

        activity()
            ->causedBy($request->user())
            ->performedOn($request->user())
            ->log('Profile photo deleted');

        return back(303)->with(['notifications' => [
            'message' => 'Profile photo deleted!',
            'type' => 'success',
        ],
        ]);
    }
}
