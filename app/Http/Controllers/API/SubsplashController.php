<?php

namespace App\Http\Controllers\API;

use App\Services\SubsplashService;
use App\Http\Controllers\Controller;

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
