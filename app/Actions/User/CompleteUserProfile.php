<?php

namespace App\Actions\User;

use App\Models\User;
use App\Data\ProfileUpdateData;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

class CompleteUserProfile
{
    use AsAction;

    public function handle(User $user, ProfileUpdateData $data, ?UploadedFile $image = null): User
    {
        if ($image) {
            $user->updateProfilePhoto($image);
        }

        $isCompletingProfile = $user->profile_step < User::PROFILE_COMPLETE;

        $hasChangedConsent = $user->opted_into_marketing !== $data->opted_into_marketing;

        $shouldSubscribe = $data->opted_into_marketing && ($isCompletingProfile || $hasChangedConsent);

        $shouldUnsubscribe = ! $data->opted_into_marketing && $hasChangedConsent;

        $attributes = [
            'given_name' => $data->given_name,
            'family_name' => $data->family_name,
            'phone' => $data->phone,
            'profile_step' => User::PROFILE_COMPLETE,
            'opted_into_marketing' => $data->opted_into_marketing,
        ];

        if ($shouldSubscribe) {
            $attributes['date_opted_into_marketing'] = now();
        }

        $user->update($attributes);

        if ($shouldSubscribe) {
            SubscribeToCommunicationListJob::dispatch(
                listId: config('services.wegive.marketing_list_id'),
                listMember: $user
            )->afterCommit();
        }

        if ($shouldUnsubscribe) {
            UnsubscribeFromCommunicationListJob::dispatch(
                donorIds: [$user->wegive_donor_id],
                user: $user,
                listId: config('services.wegive.marketing_list_id')
            )->afterCommit();
        }

        return $user;
    }
}
