<?php

namespace App\Http\Controllers\API;

use Statamic\Facades\Entry;
use App\Services\BlogService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\PreviewBuilderService;

class SearchController extends Controller
{
    public function __construct(
        protected PreviewBuilderService $previewService,
        protected BlogService $blogService
    ) {}

    public function index()
    {
        $collection = request()->query('collection', 'blogs');
        $limit = request()->query('limit', 16);
        $authorSlug = request()->query('author_slug');
        $page = request()->query('page');

        // Create a unique cache key based on the query parameters
        $cacheKey = "search_{$collection}_{$limit}_{$authorSlug}_{$page}";

        // Check if the response is already cached
        $output = Cache::remember($cacheKey, 60, function () use ($collection, $limit, $authorSlug) {
            $query = Entry::query()
                ->where('collection', $collection)
                ->where('published', true)
                ->orderBy('date', 'desc');

            if ($authorSlug && $author = $this->blogService->findAuthorBySlug($authorSlug)) {
                $query->where('author', $author->id());
            }

            $query->limit($limit)
                ->orderBy('date', 'desc');

            $entries = $query->paginate($limit);

            $output = $entries->toArray();
            $output['data'] = $entries->map(function ($entry) {
                return $this->previewService->build($entry);
            })->filter()->values();

            return $output;
        });

        return $output;
    }

    public function show(string $uri)
    {
        $uriParts = explode('/', $uri);

        if (count($uriParts) === 1) {
            return $this->blogService->handleSingleSegmentUri($uriParts[0]);
        }

        return $this->blogService->handleDoubleSegmentUri($uri);
    }
}
