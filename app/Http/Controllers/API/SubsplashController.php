<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Subsplash\SubsplashApiClient;

class SubsplashController extends Controller
{
    public function __construct(
        private SubsplashApiClient $apiClient,
    ) {}

    public function getProxy($any)
    {
        $subsplashUrl = str_replace('/api/content/subsplash', '', request()->getRequestUri());

        $baseUrl = config('services.subsplash.base_url');
        $data = $this->apiClient->fetch("{$baseUrl}/{$subsplashUrl}");

        return response()->json($data);
    }
}
