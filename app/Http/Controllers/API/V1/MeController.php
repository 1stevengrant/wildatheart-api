<?php

namespace App\Http\Controllers\API\V1;

use App\Data\UserData;
use App\Http\Controllers\Controller;

class MeController extends Controller
{
    public function __invoke(): UserData
    {
        return UserData::fromModel(auth()->user());
    }
}
