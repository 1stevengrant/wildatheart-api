<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

class UpdateUserProfileController extends Controller
{
    public function __invoke(UpdateUserProfileRequest $request)
    {
        $user = $request->user();

        if ($request->hasFile('image')) {
            $user->updateProfilePhoto($request->file('image'));
        }

        $previousOptIn = $user->opted_into_marketing;
        $newOptIn = $request->boolean('opted_into_marketing');

        $updateData = [
            'given_name' => $request->input('given_name'),
            'family_name' => $request->input('family_name'),
            'phone' => $request->input('phone'),
            'profile_step' => User::PROFILE_COMPLETE,
            'opted_into_marketing' => $newOptIn,
        ];

        if ($user->profile_step < User::PROFILE_COMPLETE && $newOptIn) {
            $updateData['date_opted_into_marketing'] = now();
            SubscribeToCommunicationListJob::dispatch(
                listId: config('services.wegive.marketing_list_id'),
                listMember: $user
            );
        }

        if ($previousOptIn !== $newOptIn) {
            $updateData['opted_into_marketing'] = $newOptIn;

            if ($newOptIn) {
                $updateData['date_opted_into_marketing'] = now();
                SubscribeToCommunicationListJob::dispatch(
                    listId: config('services.wegive.marketing_list_id'),
                    listMember: $user
                );
            } else {

                UnsubscribeFromCommunicationListJob::dispatch(
                    donorIds: [$user->wegive_donor_id],
                    user: $user,
                    listId: config('services.wegive.marketing_list_id')
                );
            }
        }

        $user->update($updateData);

        return redirect()->back()->with([
            'notifications' => [
                'message' => 'User Profile Updated!',
                'type' => 'success',
            ],
        ]);
    }
}
