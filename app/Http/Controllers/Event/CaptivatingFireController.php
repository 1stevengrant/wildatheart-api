<?php

namespace App\Http\Controllers\Event;

use App\Actions\Event\ApproveEvent;
use App\Actions\Event\CheckWelcomeStatus;
use App\Actions\Event\CreateFireEventType;
use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
