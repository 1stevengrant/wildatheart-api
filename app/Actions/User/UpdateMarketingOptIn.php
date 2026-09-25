<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

class UpdateMarketingOptIn
{
    use AsAction;

    public function handle(User $user, bool $optedIn): void
    {
        if ((bool) $user->opted_into_marketing === $optedIn) {
            return;
        }

        $user->forceFill([
            'opted_into_marketing' => $optedIn,
            'date_opted_into_marketing' => $optedIn ? now() : $user->date_opted_into_marketing,
        ])->save();

        if ($optedIn) {
            SubscribeToCommunicationListJob::dispatch(
                listId: config('services.wegive.marketing_list_id'),
                listMember: $user
            )->afterCommit();

            return;
        }

        UnsubscribeFromCommunicationListJob::dispatch(
            donorIds: [$user->wegive_donor_id],
            user: $user,
            listId: config('services.wegive.marketing_list_id')
        )->afterCommit();
    }
}
