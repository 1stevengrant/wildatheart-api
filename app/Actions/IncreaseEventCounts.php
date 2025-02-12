<?php

namespace App\Actions;

use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class IncreaseEventCounts
{
    use AsAction;

    public function handle(Event $event, string $countType): void
    {
        $event->increment($countType);
    }
}
