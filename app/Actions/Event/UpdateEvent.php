<?php

namespace App\Actions\Event;

use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateEvent
{
    use AsAction;

    public function handle(
        array $data,
        Event $event
    ): void {
        $event->update($data);
    }
}
