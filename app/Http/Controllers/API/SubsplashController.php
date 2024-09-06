<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SubsplashService;

class SubsplashController extends Controller
{
    public function getProxy($any)
    {
        $subsplashUrl = str_replace('/api/content/subsplash', '', request()->getRequestUri());

        $subsplash = new SubsplashService;
        $data = $subsplash->subsplashFetch("https://core.subsplash.com/$subsplashUrl");

        return response()->json($data);
    }
}
