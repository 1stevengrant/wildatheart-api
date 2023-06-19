<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLastLoginDateTime implements ShouldQueue
{
    public function handle(object $event): void
    {
        $event->user->last_login_at = now();
        $event->user->save();
    }
}
