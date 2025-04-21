<?php

namespace App\Http\Controllers\API\V1\Content;

use App\Http\Controllers\Controller;
use App\Services\SubsplashAppService;

class SubsplashAppController extends Controller
{
    protected $subsplashAppService;

    public function __construct(SubsplashAppService $subsplashAppService)
    {
        $this->subsplashAppService = $subsplashAppService;
    }

    public function prayers()
    {
        return response()->json($this->subsplashAppService->getPrayerList());
    }

    public function prayer($id)
    {
        return response()->json($this->subsplashAppService->getPrayer($id));
    }

    public function dailyReadings()
    {
        return response()->json($this->subsplashAppService->getDailyReadingList());
    }

    // public function dailyReading($id)
    // {
    //     return response()->json($this->subsplashAppService->getDailyReading($id));
    // }
}
