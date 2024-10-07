<?php

namespace App\Http\Resources\API;

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
            'id' => $this->id,
            'name' => $this->name,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'display_longitude' => $this->display_longitude ?? $this->longitude,
            'display_latitude' => $this->display_latitude ?? $this->latitude,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'event_type_id' => $this->event_type_id,
            'event_type_name' => $this->eventType->name,
            'event_type_image' => $this->eventType->image,
            'type_group' => $this->type_group,
            'location' => $this->location,
            'host_name' => $this->host->full_name,
            'host_photo' => $this->hostPhoto(),
            'sex' => $this->sex,
            'url' => $this->url,
            'group_size' => $this->group_size ?? 1,
        ];
    }
}
