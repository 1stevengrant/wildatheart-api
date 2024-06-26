<?php

namespace App\Jobs\Event;

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateExistingEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event, public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->event->update([
            'user_id' => $this->user->id,
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
        ]);
    }
}
