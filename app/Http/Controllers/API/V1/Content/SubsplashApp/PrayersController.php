<?php

namespace App\Http\Controllers\API\V1\Content\SubsplashApp;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\SubsplashAppService;

class PrayersController extends Controller
{
    public function __construct(private readonly SubsplashAppService $subsplashAppService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->subsplashAppService->getPrayerList());
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->subsplashAppService->getPrayer($id));
    }
}
