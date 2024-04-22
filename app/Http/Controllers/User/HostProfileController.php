<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class HostProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->profile_step == 2) {
            return to_route('host.eventType.index', ['eventType' => EventType::first()]);
        }

        return inertia('Host');
    }
}
