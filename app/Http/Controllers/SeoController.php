<?php

namespace App\Http\Controllers;

use Statamic\Entries\Entry;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Get the sitemap as a JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSitemap()
    {
        $pages = Cache::remember('sitemap_pages', 900, function () {
            return Entry::query()
                ->where('collection', '!=', 'alerts')
                ->where('uri', '!=', null)
                ->where(function ($query) {
                    $query->where('hide_from_search_engines', false)->orWhereNull('hide_from_search_engines');
                })
                ->get()->map(function ($entry) {
                    return [
                        'loc' => $entry->uri,
                        'lastmod' => $entry->last_modified->format('Y-m-d'),
                        'priority' => $entry->sitemap_priority ?? 0.5,
                    ];
                });
        });

        return response()->json(
            $pages,
        );
    }
}
