<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HostProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->profile_step == 2) {
            return to_route('host.fire');
        }

        return inertia('Host');
    }
}
