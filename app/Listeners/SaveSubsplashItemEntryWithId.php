<?php

namespace App\Listeners;

use Statamic\Facades\Entry;
use Statamic\Events\EntrySaved;

class SaveSubsplashItemEntryWithId
{
    public function handle(EntrySaved $event)
    {
        $entry = $event->entry;

        // Check if the entry has an 'id' field and it's not already set as the entry ID
        if ($entry->has('subsplash_media_item_id') && (bool) $entry->get('subsplash_media_item_id') && $entry->get('subsplash_media_item_id') != $entry->id()) {
            $newId = $entry->get('subsplash_media_item_id');

            // $prevId = $entry->id();
            // Ensure the new ID is unique
            if (! $entry->collection()->queryEntries()->where('id', $newId)->first()) {

                $newEntry = Entry::make()
                    ->blueprint($entry->blueprint())
                    ->slug($entry->slug())
                    ->collection($entry->collectionHandle())
                    ->locale($entry->locale())
                    ->published($entry->published)
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

                $newEntry->id($newId);
                $newEntry->save();
                $entry->delete();
                return redirect("/test");

                // return redirect($newEntry->edit_url());
            }
        }
    }
}
