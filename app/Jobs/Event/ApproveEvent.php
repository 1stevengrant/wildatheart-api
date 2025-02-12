<?php

namespace App\Jobs\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use App\Mail\Event\EventApproved;
use Illuminate\Support\Facades\Mail;
use App\Jobs\LookupLatitudeLongitude;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ApproveEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public User $user
    ) {}

    public function handle(): void
    {
        $this->event->update([
            'approved_at' => now(),
            'approved_by' => $this->user->id,
        ]);

        Mail::to($this->event->user->email)
            ->send(new EventApproved($this->event));

        LookupLatitudeLongitude::dispatch($this->event);

    }
}
