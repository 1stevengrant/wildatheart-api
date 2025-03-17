<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MyProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        return inertia('Profile/MyProfilePage');
    }
}
