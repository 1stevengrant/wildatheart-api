<?php

namespace App\Http\Controllers\API\Media;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;

class MediaItemsBySeriesController extends Controller
{
    public function show(string $seriesId): JsonResponse
    {
        $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
            ->inSeries($seriesId)
            ->orderBy('data->position', 'asc')
            ->get();

        return response()->json($mediaItemsInSeries);
    }
}
