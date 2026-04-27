<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\ExperienceResponseBuilder;

class ExperienceDataController extends Controller
{
    public function __construct(private readonly ExperienceResponseBuilder $responseBuilder) {}

    public function show(string $slug): JsonResponse
    {
        return response()->json($this->responseBuilder->buildBySlug($slug));
    }
}
