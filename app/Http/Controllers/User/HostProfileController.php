<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->profile_step == User::PROFILE_COMPLETE) {
            return to_route('host.eventType.select');
        }

        return inertia('HostPage');
    }
}
