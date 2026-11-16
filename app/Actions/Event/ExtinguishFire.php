<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\Fire\ConfirmFireUnpublishedMailable;

class ExtinguishFire
{
    use AsAction;

    public function handle(Event $fire, User $causer): void
    {
        $extinguished = DB::transaction(function () use ($fire): bool {
            $lockedFire = Event::query()->lockForUpdate()->findOrFail($fire->getKey());

            if ($lockedFire->hasBeenExtinguishedByHost()) {
                return false;
            }

            $lockedFire->update([
                'approved_at' => null,
                'auto_unpublished' => false,
            ]);

            return true;
        });

        $fire->refresh();

        if (! $extinguished) {
            return;
        }

        LogActivityJob::dispatch(
            description: 'Fire unpublished',
            causedBy: $causer,
            performedOn: $fire,
        );

        collect([$fire->user])
            ->merge($fire->managers)
            ->each(fn (User $recipient) => Mail::to($recipient->email)->send(new ConfirmFireUnpublishedMailable($fire)));
    }
}
