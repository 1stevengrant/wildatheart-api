<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\V1\AttendeeData;
use App\Data\ConnectWithHostData;
use App\Http\Controllers\Controller;
use App\Actions\Event\RegisterEventAttendee;

class AttendeeController extends Controller
{
    public function store(Event $event, ConnectWithHostData $data)
    {
        $attendee = RegisterEventAttendee::run($event, $data);

        return AttendeeData::fromAttendee($attendee);
    }
}
