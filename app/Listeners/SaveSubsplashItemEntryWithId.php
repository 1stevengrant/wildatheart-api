<?php

namespace App\Listeners;

use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry;

class SaveSubsplashItemEntryWithId
{
    public function handle(EntrySaved $event)
    {
        $entry = $event->entry;

        // Check if the entry has an 'id' field and it's not already set as the entry ID
        if ($entry->has('subsplash_media_item_id') && $entry->get('subsplash_media_item_id') != $entry->id()) {
            $newId = $entry->get('subsplash_media_item_id');

            // $prevId = $entry->id();
            // Ensure the new ID is unique
            if (! $entry->collection()->queryEntries()->where('id', $newId)->first()) {
                $newEntry = Entry::make()
                    ->blueprint('subsplash_media_item')
                    ->slug($entry->slug())
                    ->collection($entry->collectionHandle())
                    ->locale($entry->locale());

                $data = $entry->data()->except(['id'])->all();
                $newEntry->data($data);

                $newEntry->id($newId);
                $newEntry->save();
                $entry->delete();

                return redirect($newEntry->edit_url());
                // $oldEntry = Entry::find($prevId);
                // if ($oldEntry) {
                //     $oldEntry->delete();
                // }
            }
        }
    }
}
