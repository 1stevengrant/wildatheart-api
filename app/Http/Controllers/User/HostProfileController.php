<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->profile_step == 2) {
            return to_route('host.eventType.select');
        }

        return inertia('Host');
    }
}
