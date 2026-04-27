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

        return $response;
    }
}
