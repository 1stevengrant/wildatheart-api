<?php

namespace App\Services;

use Statamic\Entries\Entry;

class EventResponseBuilder
{
    public function __construct(private readonly EntryBuilderService $entryBuilder) {}

    public function build(Entry $event): array
    {
        $response = $this->entryBuilder->buildResponseFromEntry(
            $event->blueprint->handle === 'video_study' ? $event->related_content : $event
        );

        if (isset($response['background_video_url']) && is_array($response['background_video_url'])) {
            $response['background_video_url'] = $response['background_video_url']['url'];
        }

        if (isset($response['featured_video_url']) && is_array($response['featured_video_url'])) {
            $response['featured_video_url'] = $response['featured_video_url']['url'];
        }

        // A video_study landing page lives on the linked experiences entry,
        // where the logo is stored under experience_logo. Consumers should not
        // have to know which blueprint they were served.
        $response['event_logo'] ??= $response['experience_logo'] ?? null;

        return $response;
    }
}
