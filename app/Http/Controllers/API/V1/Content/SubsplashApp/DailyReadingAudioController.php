<?php

namespace App\Http\Controllers\API\V1\Content\SubsplashApp;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\SubsplashAppService;

class DailyReadingAudioController extends Controller
{
    public function __construct(private readonly SubsplashAppService $subsplashAppService) {}

    public function show(?string $id = null): JsonResponse
    {
        return response()->json($this->subsplashAppService->getDailyReadingAudio($id));
    }
}
