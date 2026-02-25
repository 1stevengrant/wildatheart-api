<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Data\V1\UserData;
use App\Http\Controllers\Controller;

class MeController extends Controller
{
    public function __invoke()
    {
        return UserData::fromModel(auth()->user());
    }
}
