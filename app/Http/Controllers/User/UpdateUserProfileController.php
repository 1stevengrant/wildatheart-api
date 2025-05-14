<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Notifications\EmailChangeVerificationNotification;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

class UpdateUserProfileController extends Controller
{
    public function __invoke(UpdateUserProfileRequest $request)
    {
        $user = $request->user();
        $message = 'No changes were made to your profile.';
        $hasChanges = false;

        // Handle profile photo upload
        if ($request->hasFile('image')) {
            $user->updateProfilePhoto($request->file('image'));
            $hasChanges = true;
            $message = 'User Profile Updated!';
        }

        // Handle email change with verification
        if ($request->filled('new_email') && $request->new_email !== $user->email) {
            $user->update([
                'pending_email' => $request->new_email,
                'email_verification_token' => Str::random(64),
            ]);

            $user->notify(new EmailChangeVerificationNotification($user->email_verification_token));

            $hasChanges = true;
            $message = 'Your profile has been updated. Please check your new email address to verify the change.';
        }

        $fieldsToUpdate = [];

        // Track changes for individual fields
        if ($request->given_name !== $user->given_name) {
            $fieldsToUpdate['given_name'] = $request->given_name;
            $hasChanges = true;
        }

        if ($request->family_name !== $user->family_name) {
            $fieldsToUpdate['family_name'] = $request->family_name;
            $hasChanges = true;
        }

        if ($request->phone !== $user->phone) {
            $fieldsToUpdate['phone'] = $request->phone;
            $hasChanges = true;
        }

        // Handle marketing opt-in/opt-out with WeGive integration
        $previousOptIn = $user->opted_into_marketing;
        $newOptIn = $request->boolean('opted_into_marketing');

        if ($newOptIn !== $previousOptIn) {
            $fieldsToUpdate['opted_into_marketing'] = $newOptIn;
            $hasChanges = true;

            if ($newOptIn) {
                $fieldsToUpdate['date_opted_into_marketing'] = now();
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

        // Handle profile completion step
        if ($user->profile_step < User::PROFILE_COMPLETE) {
            $fieldsToUpdate['profile_step'] = User::PROFILE_COMPLETE;
            $hasChanges = true;

            // If user is newly opting into marketing during profile completion
            if ($newOptIn && !$previousOptIn && !isset($fieldsToUpdate['date_opted_into_marketing'])) {
                $fieldsToUpdate['date_opted_into_marketing'] = now();
                SubscribeToCommunicationListJob::dispatch(
                    listId: config('services.wegive.marketing_list_id'),
                    listMember: $user
                );
            }
        }

        // Update only if there are changes
        if (!empty($fieldsToUpdate)) {
            $user->update($fieldsToUpdate);
            if ($message === 'No changes were made to your profile.') {
                $message = 'User Profile Updated!';
            }
        }

        return redirect()->back()->with([
            'notifications' => [
                'message' => $message,
                'type' => $hasChanges ? 'success' : 'info',
            ],
        ]);
    }
}
