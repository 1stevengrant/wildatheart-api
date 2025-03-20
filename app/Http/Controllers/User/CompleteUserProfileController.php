<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompleteUserProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        return User::query()->withCompleteProfilesAndEvents()->get();
    }
}
