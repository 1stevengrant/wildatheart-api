<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $pages = Cache::remember('sitemap_pages.v2', 900, function (): array {
            return EntryQueryBuilder::query()
                ->where('collection', '!=', 'alerts')
                ->where('uri', '!=', null)
                ->where(function ($query) {
                    $query->where('hide_from_search_engines', false)->orWhereNull('hide_from_search_engines');
                })
                ->get()->map(function ($entry): array {
                    return [
                        'loc' => $entry->uri,
                        'lastmod' => $entry->last_modified->format('Y-m-d'),
                        'priority' => $entry->sitemap_priority ?? 0.5,
                    ];
                })->toArray();
        });

        return response()->json($pages);
    }
}
