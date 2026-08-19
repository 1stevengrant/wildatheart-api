<?php

namespace App\Services;

use Statamic\Entries\Entry;

class EventResponseBuilder
{
    public function __construct(private readonly EntryBuilderService $entryBuilder) {}

    public function build(Entry $event): array
    {
        // A video_study entry points at the experiences entry holding the real
        // landing page. An unlinked or dangling pointer augments to null, so
        // fall back to the entry itself: a sparse landing page built from its
        // own title and slug beats a 500.
        $source = $event->blueprint->handle === 'video_study'
            ? ($event->related_content ?? $event)
            : $event;

        $response = $this->entryBuilder->buildResponseFromEntry($source);

        if ($source !== $event) {
            $eventResponse = $this->entryBuilder->buildResponseFromEntry($event);
            $response['topics'] = $eventResponse['topics'] ?? [];
            $response['blocks'] = $this->videoStudyBlocks($response, $eventResponse);
        }

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

    private function videoStudyBlocks(array $relatedResponse, array $eventResponse): array
    {
        if (! empty($relatedResponse['blocks'])) {
            return $relatedResponse['blocks'];
        }

        if (! empty($eventResponse['blocks'])) {
            return $eventResponse['blocks'];
        }

        $blocks = [];

        if (! empty($relatedResponse['hero_title'])) {
            $blocks[] = [
                'hero_title' => $relatedResponse['hero_title'],
                'type' => 'hero_title',
            ];
        }

        return [
            ...$blocks,
            ...($relatedResponse['hero_subtitle'] ?? []),
        ];
    }
}
