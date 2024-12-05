<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;

class CaptivatingFireController extends AbstractFireEventController
{
    protected function getEventType(): string
    {
        return EventType::CAPTIVATING_FIRE;
    }

    protected function getViewName(): string
    {
        return 'HostCaptivatingFirePage';
    }
}
