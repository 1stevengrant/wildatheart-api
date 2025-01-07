<?php

namespace App\Listeners;

use Statamic\Facades\Entry;
use Statamic\Events\EntrySaved;

class SaveSubsplashItemEntryWithId
{
    public function handle(EntrySaved $event)
    {
        $entry = $event->entry;

        // I'm not sure it's best to do this on save
        // if ($entry->collection->handle == "media_series") {
        //     $seriesId = $entry->id;
        //     $relatedMediaItems = Entry::query()->where('collection', "media_item")->where('data->series', 'like', "%{$seriesId}")->get();

        //     $content_types = $entry->content_type;

        //     foreach ($relatedMediaItems as $mediaItem) {
        //         $mediaItem->set("content_type", $content_types->pluck('slug'));
        //         $mediaItem->save();
        //     }
        // }

        // if it isn't set, do nothing
        if (! $entry->has('subsplash_media_item_id')) {
            return;
        }

        // Check if the entry has an 'id' field and it's not already set as the entry ID
        $mediaItemId = $entry->get('subsplash_media_item_id');

        // Ensure that this item hasn't already been created with its id yet.
        if ($mediaItemId === $entry->id() || $entry->collection()->queryEntries()->where('id', $mediaItemId)->first()) {
            return;
        }

        // Duplicate existing entry to new entry
        $newEntry = Entry::make()
            ->blueprint($entry->blueprint())
            ->slug($entry->slug())
            ->collection($entry->collectionHandle())
            ->locale($entry->locale())
            ->published($entry->published)
            ->id($mediaItemId)
            ->date($entry->date);

        // Point any previous relationships to this new entry
        $relatedEntries = Entry::query()
            ->where('data', 'like', "%{$entry->id}%")
            ->get();

        foreach ($relatedEntries as $relatedEntry) {
            $data = $relatedEntry->data()->all();
            $data = str_replace($entry->id, $newEntry->id, json_encode($data));
            $data = json_decode($data, true);
            $relatedEntry->data($data);
            $relatedEntry->save();
        }

        $data = $entry->data()->except(['id'])->all();
        $newEntry->data($data);
        $newEntry->save();

        // Remove the old entry
        $entry->delete();
    }
}
