<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;

class UpdateUserProfileController extends Controller
{
    public function __invoke(UpdateUserProfileRequest $request)
    {

        if ($request->hasFile('image')) {
            $request->user()->updateProfilePhoto($request->file('image'));
        }

        auth()->user()->update([
            'given_name' => request('given_name'),
            'family_name' => request('family_name'),
            'phone' => request('phone'),
            'opted_into_marketing' => request('opted_into_marketing'),
            'profile_step' => 2,
        ]);

        return redirect()->back()->with([
            'notifications' => [
                'message' => 'User Profile Updated!',
                'type' => 'success',
            ],
        ]);
    }
}
