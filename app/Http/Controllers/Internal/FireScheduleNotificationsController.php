<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Actions\Internal\ResetFireScheduleNotifications;

class FireScheduleNotificationsController extends Controller
{
    public function destroy(Event $event): RedirectResponse
    {
        ResetFireScheduleNotifications::run($event);

        return back()->with('status', "{$event->name}: forgot its sent schedule emails and republished it if the schedule had unpublished it.");
    }
}
