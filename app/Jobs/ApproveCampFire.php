<?php

namespace App\Jobs;

use App\Mail\Campfires\CampfireApproved;
use App\Models\Campfire;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ApproveCampFire implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Campfire $campfire,
        public User $user
    ) {
    }

    public function handle(): void
    {
        $this->campfire->update([
            'approved_at' => now(),
            'approved_by' => $this->user->id,
        ]);

        Mail::to($this->campfire->user->email)
            ->send(new CampfireApproved($this->campfire));

        LookupLatitudeLongitude::dispatch($this->campfire);

    }
}
