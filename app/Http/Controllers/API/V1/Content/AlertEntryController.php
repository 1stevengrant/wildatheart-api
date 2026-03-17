<?php

namespace App\Http\Controllers\API\V1\Content;

use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\DataTypeObjects\API\V1\Content\AlertResponse;

class AlertEntryController extends Controller
{
    public function index()
    {
        $entries = EntryQueryBuilder::alerts()
            ->published()
            ->where('start_date', '<=', now()->format('Y-m-d H:i:s'))
            ->where('end_date', '>=', now()->format('Y-m-d H:i:s'))
            ->orderBy('start_date', 'desc')
            ->get();

        if ($entries->isEmpty()) {
            return AlertResponse::collect([]);
        }

        $entries = $entries->map(function ($entry) {
            $entry = $entry->toArray();
            $entry['link_url'] = $entry['link_url'] ? $entry['link_url']['url'] : null;

            return $entry;
        });

        return AlertResponse::collect($entries);
    }
}
