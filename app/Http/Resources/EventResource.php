<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'host' => $this->host_name,
            'hostObject' => HostResource::make($this->user)->resolve(),
            'hostFriendlyName' => $this->host->given_name,
            'location' => $this->location,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'hostPhoto' => $this->hostAvatar(),
            'url' => $this->url,
            'id' => $this->id,
            'eventType' => $this->eventType->slug,
            'eventTypeImage' => $this->eventType->image,
            'eventTypeData' => $this->eventType,
            'hostId' => $this->host->id,
            'latitude' => floatval($this->latitude),
            'longitude' => floatval($this->longitude),
            'gift_requested' => $this->hasRequestedGift(),
            'approved_at' => $this->approved_at,
            'startDate' => $this->start_date ? $this->start_date->format('M j') : null,
            'endDate' => $this->end_date ? $this->end_date->format('M j') : null,
            'heroImage' => $this->hero_image ?? null,
            'heroVideo' => $this->hero_video ?? null,
        ];
    }
}
