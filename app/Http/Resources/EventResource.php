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
        ray($this->title);

        return [
            'host' => $this->host_name,
            'host_object' => HostResource::make($this->user)->resolve(),
            'host_friendly_name' => $this->user->given_name,
            'location' => $this->location,
            'name' => $this->title,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'host_photo' => $this->user?->profilePhoto(),
            'url' => $this->url,
            'id' => $this->id,
            'event_type' => $this->eventType?->slug,
            'event_type_image' => $this->eventType?->image,
            'event_type_data' => $this->eventType,
            'host_id' => $this->user->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'gift_requested' => $this->hasRequestedGift(),
            'approved_at' => $this->approved_at,
            'startDate' => $this->start_date ?? null,
            'start_date' => $this->start_date ?? null,
            'endDate' => $this->end_date ?? null,
            'end_date' => $this->end_date ?? null,
            'hero_image' => $this->hero_image ?? null,
            'hero_video' => $this->hero_video ?? null,
            'is_private' => $this->is_private,
            'type_group' => $this->whenLoaded('eventType', function () {
                return $this->eventType->group_type;
            }, null),
            'sex' => $this->sex,
        ];
    }
}
