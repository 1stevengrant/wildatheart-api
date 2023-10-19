<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UpdateUserProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        request()->validate([
            'given_name' => 'required|string|max:255|min:2',
            'family_name' => 'required|string|max:255|min:2',
            'phone' => 'required|max:15|min:9',
            'opted_into_marketing' => 'required|boolean',
            'image' => 'nullable|image|max:1024|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('image')) {
            ray('has file');
            $request->user()->updateProfilePhoto($request->file('image'));
        }

        auth()->user()->update([
            'given_name' => request('given_name'),
            'family_name' => request('family_name'),
            'phone' => request('phone'),
            'opted_into_marketing' => request('opted_into_marketing'),
            'profile_step' => 2,
        ]);

        return redirect()->back()->with(['notifications' => [
            'message' => 'User Profile Updated!',
            'type' => 'success',
        ],
        ]);
    }
}
