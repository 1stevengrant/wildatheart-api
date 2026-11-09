<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Actions\Internal\MoveFireScheduleDates;
use App\Http\Requests\Internal\MoveFireScheduleRequest;

class FireScheduleMovesController extends Controller
{
    public function store(MoveFireScheduleRequest $request, Event $event): RedirectResponse
    {
        $offset = MoveFireScheduleDates::run($event, $request->preset());

        return back()->with('status', $offset === 0
            ? "{$event->name}: the last date is already {$request->preset()->label()}, nothing moved."
            : "{$event->name}: moved every date by {$offset} days so the last date is {$request->preset()->label()}.");
    }
}
