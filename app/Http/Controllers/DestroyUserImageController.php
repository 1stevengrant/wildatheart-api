<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DestroyUserImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->user()->deleteProfilePhoto();

        return back(303)->with(['notifications' => [
            'message' => 'Profile photo deleted!',
            'type' => 'success',
        ],
        ]);
    }
}
