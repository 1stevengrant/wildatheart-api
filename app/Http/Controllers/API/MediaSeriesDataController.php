<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\Subsplash\SubsplashMediaSeriesResponseBuilder;

class MediaSeriesDataController extends Controller
{
    public function __construct(private readonly SubsplashMediaSeriesResponseBuilder $responseBuilder) {}

    public function show(string $slug): mixed
    {
        $series = EntryQueryBuilder::mediaSeries()
            ->bySlug($slug)
            ->firstOrFail();

        if ($series->blueprint === 'custom_series') {
            return $series;
        }

        $response = $this->responseBuilder->build($series);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        return response()->json($response);
    }
}
