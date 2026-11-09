<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Actions\Internal\RunFireScheduleCheck;

class FireScheduleChecksController extends Controller
{
    public function store(Event $event): RedirectResponse
    {
        return back()
            ->with('status', "{$event->name}: " . RunFireScheduleCheck::run($event))
            ->with('showCapturedEmailsLink', true);
    }
}
