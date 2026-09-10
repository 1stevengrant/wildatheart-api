<?php

namespace App\Actions\Event;

use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEvent
{
    use AsAction;

    public function handle(Event $event): void
    {
        $event->delete();
    }
}
