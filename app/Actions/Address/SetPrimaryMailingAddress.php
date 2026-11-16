<?php

namespace App\Actions\Address;

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Data\MailingAddressData;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class SetPrimaryMailingAddress
{
    use AsAction;

    public function handle(User $user, MailingAddressData $address, ?Event $event = null): Address
    {
        return DB::transaction(function () use ($user, $address, $event): Address {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $user->refresh();

            $user->addresses()->update(['is_primary' => false]);

            if (empty($user->family_name) || empty($user->given_name)) {
                $user->update([
                    'given_name' => $address->given_name,
                    'family_name' => $address->family_name,
                    'name' => "{$address->given_name} {$address->family_name}",
                ]);
            }

            return $user->addresses()->create([
                ...$address->toArray(),
                'event_id' => $event?->id,
                'is_primary' => true,
            ]);
        });
    }
}
