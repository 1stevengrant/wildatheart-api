<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventAPIResource extends JsonResource
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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'latitude' => floatval($this->latitude),
            'longitude' => floatval($this->longitude),
            'url' => $this->url,
            'event_type_id' => $this->eventType->id,
            'event_type_name' => $this->eventType->name,
            'event_type_image' => $this->eventType->image ?? null,
            'location' => $this->location,
            'host_photo' => $this->hostPhoto(),
            'host_full_name' => $this->hostFullName(),
            'host_md5_email' => md5($this->host->email),
            'type_group' => $this->type_group,
            'sex' => $this->sex,
        ];
    }
}
