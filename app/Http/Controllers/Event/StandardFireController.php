<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;

class StandardFireController extends AbstractFireEventController
{
    protected function getEventType(): string
    {
        return EventType::FIRES;
    }

    protected function getViewName(): string
    {
        return 'HostFirePage';
    }
}
