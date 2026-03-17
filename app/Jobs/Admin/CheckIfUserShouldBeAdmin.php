<?php

namespace App\Jobs\Admin;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckIfUserShouldBeAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (
            str_contains($this->user->email, '@wildatheart.org') ||
            str_contains($this->user->email, '@ghijk.co.uk')
        ) {
            $this->user->assignRole(UserRoleEnum::Admin);
        }
    }
}
