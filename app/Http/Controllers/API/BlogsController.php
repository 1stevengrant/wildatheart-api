<?php

namespace App\Http\Controllers\API;

use Statamic\Facades\Entry;
use App\Http\Controllers\Controller;
use Statamic\Entries\Entry as StatamicEntry;
use App\DataTypeObjects\API\V1\Content\BlogResponse;
use App\Services\BlogService;


class BlogsController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    public function index()
    {
        return Entry::query()
            ->where('collection', 'blogs')
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($entry) {
                return $this->blogService->createBlogResponse($entry);
            });
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
