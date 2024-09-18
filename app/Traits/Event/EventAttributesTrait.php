<?php

namespace App\Traits\Event;

use App\Http\Resources\HostResource;
use App\Models\EventType;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;

trait EventAttributesTrait
{
    public function getCoordinatesAttribute(): string
    {
        return $this->longitude.', '.$this->latitude;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getUrlAttribute(): string
    {
        return route('view.event', ['event' => $this]);
    }

    public function getHostNameAttribute()
    {
        return $this->host->full_name;
    }

    public function getTitleAttribute(): ?string
    {
        if ($this->name) {
            return $this->name.' Fire';
        }

        return null;
    }

    public function setStateCodeAttribute($value): void
    {
        $this->attributes['state'] = getStateAbbreviation(strtoupper($value));
    }

    public function getAdminUrlAttribute(): \Illuminate\Foundation\Application|string|UrlGenerator|Application
    {
        return url('manager/resources/camp-fires/'.$this->id);
    }

    public function getTypeGroupAttribute(): string
    {
        $eventName = strtolower($this->eventType->name);

        if (str_contains($eventName, EventType::TYPE_RETREAT) || str_contains($eventName, 'school')) {
            return EventType::TYPE_RETREAT;
        }

        if (str_contains($eventName, EventType::TYPE_EXPERIENCE)) {
            return EventType::TYPE_EXPERIENCE;
        }

        return EventType::TYPE_FIRE;

    }

    public function getEventTypeImageAttribute(): string
    {
        return $this->eventType->image ?? '';
    }

    public function getHostPhotoAttribute(): ?string
    {
        return $this->host->image ?? null;
    }


    public function getStartDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getHostObjectAttribute()
    {
        return HostResource::make($this->host);
    }
}
