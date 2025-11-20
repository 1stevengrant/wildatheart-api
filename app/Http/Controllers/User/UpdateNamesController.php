<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserNamesRequest;

class UpdateNamesController extends Controller
{
    /**
     * Display the name update form.
     */
    public function show(Request $request, User $user)
    {
        // Store user UUID in session to validate form submission
        session(['profile_update_user_uuid' => $user->uuid]);

        return inertia('Profile/UpdateNamesPage', [
            'user' => [
                'uuid' => $user->uuid,
                'email' => $user->email,
                'given_name' => $user->given_name,
                'family_name' => $user->family_name,
            ],
        ]);
    }

    /**
     * Update the user's name.
     */
    public function update(UpdateUserNamesRequest $request, User $user)
    {
        // Verify this user UUID matches the one from the signed URL
        $allowedUserUuid = session('profile_update_user_uuid');

        if (! $allowedUserUuid || $allowedUserUuid !== $user->uuid) {
            abort(403, 'Unauthorized to update this user.');
        }

        $user->update([
            'given_name' => $request->input('given_name'),
            'family_name' => $request->input('family_name'),
        ]);

        // Clear the session
        session()->forget('profile_update_user_uuid');

        return redirect()->route('home')->with([
            'notifications' => [
                'message' => 'Your profile has been updated successfully!',
                'type' => 'success',
            ],
        ]);
    }
}
