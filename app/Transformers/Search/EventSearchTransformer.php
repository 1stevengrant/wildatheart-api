<?php

namespace App\Transformers\Search;

class EventSearchTransformer
{
    public function transform($searchable, array $array): array
    {
        return [
            'objectID' => $searchable->id,
            'id' => $searchable->id,
            'coreEventType' => $this->getCoreEventType($searchable->eventType),
            'eventType' => $searchable->eventType->slug,
            'eventTypeImage' => $searchable->eventType->image ?? null,
            'city' => $searchable->city,
            'state' => $searchable->state,
            'country' => $searchable->country,
            'name' => $searchable->name,
            'host' => $searchable->hostName,
            'location' => $searchable->location,
            'hostPhoto' => $searchable->hostAvatar(),
            'url' => $searchable->url,
            '_geoloc' => [
                'lat' => floatval($searchable->latitude),
                'lng' => floatval($searchable->longitude),
            ],
            'sex' => $searchable->sex,
            'startDate' => $searchable->start_date ? $searchable->start_date->format('M j') : null,
            'startDateTimestamp' => $searchable->start_date ? $searchable->start_date->timestamp * 1000 : null,
            'endDate' => $searchable->end_date ? $searchable->end_date->format('M j') : null,
            'endDateTimestamp' => $searchable->end_date ? $searchable->end_date->timestamp * 1000 : null,
        ];
    }

    public function getCoreEventType($eventType): string
    {
        if (str_contains($eventType->name, 'retreat') || str_contains($eventType->name, 'school')) {
            return 'retreat';
        }

        if (str_contains($eventType->name, 'experience')) {
            return 'experience';
        }

        return 'fire';
    }
}
