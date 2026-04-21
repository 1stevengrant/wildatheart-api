<?php

namespace App\Http\Controllers\API\Media;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;

class MediaItemController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $mediaItem = EntryQueryBuilder::mediaItems()
            ->bySlug($slug)
            ->firstOrFail();

        $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
            ->inSeries($mediaItem->series)
            ->get();

        $mediaItem['mediaItems'] = $mediaItemsInSeries->toArray();

        return response()->json($mediaItem);
    }
}
