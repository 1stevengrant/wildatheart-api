<?php

namespace App\Transformers;

class EventInertiaTransformer
{
    public function transform($searchable, array $array): array
    {
        return [
            'host' => $searchable->host_name,
            'hostFriendlyName' => $searchable->host->given_name,
            'location' => $searchable->location,
            'name' => $searchable->name,
            'city' => $searchable->city,
            'state' => $searchable->state,
            'country' => $searchable->country,
            'hostPhoto' => $searchable->hostAvatar(),
            'url' => $searchable->url,
            'id' => $searchable->id,
            'eventType' => $searchable->eventType->slug,
            'eventTypeImage' => $searchable->eventType->image,
            'eventTypeData' => $searchable->eventType,
            'hostId' => $searchable->host->id,
            'latitude' => floatval($searchable->latitude),
            'longitude' => floatval($searchable->longitude),
            'gift_requested' => $searchable->hasRequestedGift(),
            'approved_at' => $searchable->approved_at,
            'startDate' => $searchable->start_date ? $searchable->start_date->format('M j') : null,
            'endDate' => $searchable->end_date ? $searchable->end_date->format('M j') : null,
            'heroImage' => $searchable->hero_image ?? null,
            'heroVideo' => $searchable->hero_video ?? null,
        ];
    }
}
