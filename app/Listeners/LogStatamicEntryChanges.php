<?php

namespace App\Listeners;

use Statamic\Facades\Entry;
use App\Jobs\LogActivityJob;
use Statamic\Events\EntrySaving;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntryDeleted;
use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Contracts\Auth\Authenticatable;

class LogStatamicEntryChanges
{
    public const LOG_NAME = 'entries';

    public function handle(object $event): void
    {
        ray($event);
        $entry = $event->entry;
        $user = auth()->user() ?? 'System';
        if ($event instanceof EntrySaving) {
            $this->entrySaving(entry: $entry, user: $user);
        }

        if ($event instanceof EntryCreated) {
            dispatch(new LogActivityJob(
                logName: self::LOG_NAME,
                description: 'Entry created by ' . ($user instanceof Authenticatable ? $user->fullname : $user),
                properties: $entry->data()->toArray(),
                causedBy: $user,
            ));
        }

        if ($event instanceof EntryDeleted) {

            dispatch(new LogActivityJob(
                logName: self::LOG_NAME,
                description: 'Entry deleted by ' . ($user instanceof Authenticatable ? $user->fullname : $user),
                properties: $entry->data()->toArray(),
                causedBy: $user,
            ));
        }
    }

    private function entrySaving($entry, $user)
    {
        $currentEntry = Entry::query()->find($entry->id());

        if (! $currentEntry) {
            return;
        }

        $existingContent = $currentEntry->data()->toArray();
        $updatedContent = $entry->data()->toArray();

        unset($existingContent['updated_at'], $updatedContent['updated_at']);

        $differences = [];
        foreach ($updatedContent as $key => $value) {
            if (! array_key_exists($key, $existingContent)) {
                $differences[$key] = [
                    'old' => null,
                    'new' => $value,
                ];
            } else {
                $differences[$key] = [
                    'old' => $existingContent[$key],
                    'new' => $value,
                ];
            }
        }

        $differences = array_filter($differences, function ($diff) {
            return ! deepArrayCompare($diff['new'], $diff['old']);
        });

        if (empty($differences)) {
            return;
        }

        dispatch(new LogActivityJob(
            logName: self::LOG_NAME,
            description: 'Entry updated by ' . ($user instanceof Authenticatable ? $user->fullname : $user),
            properties: $differences,
            causedBy: $user,
            tap: function (Activity $activity) use ($entry) {
                $activity->statamic_entry_id = $entry->id();
            }
        ));
    }
}
