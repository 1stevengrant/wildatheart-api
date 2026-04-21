<?php

namespace App\Http\Controllers\API\Media;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\Subsplash\SubsplashMediaItemResponseBuilder;

class SubsplashMediaItemController extends Controller
{
    public function __construct(private readonly SubsplashMediaItemResponseBuilder $responseBuilder) {}

    public function show(string $slug): JsonResponse
    {
        $mediaItem = EntryQueryBuilder::mediaItems()
            ->bySlug($slug)
            ->firstOrFail();

        $response = $this->responseBuilder->build($mediaItem);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        return response()->json($response);
    }
}
