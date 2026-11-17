<?php

namespace App\Http\Controllers\User;

use App\Data\ProfileUpdateData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Actions\User\CompleteUserProfile;
use App\Http\Requests\UpdateUserProfileRequest;

class UpdateUserProfileController extends Controller
{
    public function __invoke(UpdateUserProfileRequest $request): RedirectResponse
    {
        CompleteUserProfile::run(
            $request->user(),
            ProfileUpdateData::fromRequest($request),
            $request->file('image'),
        );

        return redirect()->back()->with([
            'notifications' => [
                'message' => 'User Profile Updated!',
                'type' => 'success',
            ],
        ]);
    }
}
