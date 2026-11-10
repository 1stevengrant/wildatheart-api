<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Queries\SitemapPagesQuery;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(SitemapPagesQuery $sitemapPages): JsonResponse
    {
        $pages = Cache::flexible('sitemap_pages.v3', [900, 86400], fn (): array => $sitemapPages->get());

        return response()->json($pages);
    }
}
