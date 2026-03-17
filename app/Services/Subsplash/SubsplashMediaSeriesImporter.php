<?php

namespace App\Services\Subsplash;

use Carbon\Carbon;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class SubsplashMediaSeriesImporter
{
    public function __construct(
        private SubsplashApiClient $apiClient,
    ) {}

    public function import(array $mediaSeries): ?Entry
    {
        if (! $mediaSeries['id']) {
            return null;
        }

        $entry = EntryFacade::query()
            ->where('collection', 'media_series')
            ->where('id', $mediaSeries['id'])
            ->orWhereJsonContains('data->subsplash_media_series_id', $mediaSeries['id'])
            ->first();

        if ($entry && $entry->subsplash_media_series_id != $entry->id) {

            $duplicate = EntryFacade::make()
                ->collection('media_series')
                ->published(true)
                ->slug($entry->slug)
                ->blueprint($entry->blueprint())
                ->id($mediaSeries['id']);

            $duplicate->data($entry->data()->all());
            $duplicate->save();

            $relatedEntries = EntryFacade::query()
                ->where('data', 'like', "%{$entry->id}%")
                ->get();

            foreach ($relatedEntries as $relatedEntry) {
                $data = $relatedEntry->data()->all();
                $data = str_replace($entry->id, $duplicate->id, json_encode($data));
                $data = json_decode($data, true);
                $relatedEntry->data($data);
                $relatedEntry->save();
            }

            $entry->delete();

            return null;
        }

        if (! $entry) {
            $entry = EntryFacade::make()
                ->published(true)
                ->blueprint('subsplash_media_series')
                ->collection('media_series')
                ->id($mediaSeries['id'])
                ->slug($mediaSeries['title']);

            $entry->date(Carbon::parse($mediaSeries['published_at']));

            $entry->data([
                'title' => $mediaSeries['title'],
                'subsplash_media_series_id' => $mediaSeries['id'],
            ]);

            $entry->save();
        }

        return $entry;
    }

    public function findInStatamic(string $seriesId): ?Entry
    {
        return EntryFacade::query()
            ->where('collection', 'media_series')
            ->where('id', $seriesId)
            ->orWhere(function ($query) use ($seriesId) {
                $query->where('blueprint', 'custom_series')
                    ->where('subsplash_media_series_id', $seriesId);
            })
            ->orWhere(function ($query) use ($seriesId) {
                $query->where('collection', 'experiences')
                    ->where('subsplash_series_id', $seriesId);
            })
            ->first();
    }
}
