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
        public Campfire $campFire,
        public User $user
    ) {
    }

    public function handle(): void
    {
        $this->campFire->update([
            'approved_at' => now(),
            'approved_by' => $this->user->id,
        ]);

        Mail::to($this->campFire->user->email)
            ->send(new CampfireApproved($this->campFire));

        LookupLatitudeLongitude::dispatch($this->campFire);

    }
}
