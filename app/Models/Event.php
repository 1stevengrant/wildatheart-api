<?php

namespace App\Models;

use App\Builders\EventBuilder;
use App\Enums\EventSexEnum;
use App\Traits\Event\EventActionsTrait;
use App\Traits\Event\EventAttributesTrait;
use App\Traits\Event\EventRelationshipsTrait;
use App\Traits\Event\EventScopeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends BaseModel
{
    use EventActionsTrait, EventAttributesTrait, EventRelationshipsTrait, EventScopeTrait, HasFactory, HasUuids, SoftDeletes;

    protected $casts = [
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'legacy_drupal_location' => 'array',
        'longitude' => 'float',
        'latitude' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'welcome_sent' => 'boolean',
        'gift_requested' => 'boolean',
        'sex' => EventSexEnum::class,
    ];

    protected $appends = ['url', 'type_group', 'event_type_image', 'host_name', 'host_photo', 'location', 'hostObject'];

    protected $hidden = ['user_id', 'host', 'eventType'];

    const FOR_MEN = 'men';

    const FOR_WOMEN = 'women';

    const DEFAULT_COUNTRY = 'us';

    public function isHost(): bool
    {
        return auth()->check() && $this->user->id === auth()->user()->id;
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved_at;
    }

    public function hasRequestedGift(): bool
    {
        return (bool) $this->gift_requested;
    }

    public function hasAddress(): bool
    {
        return (bool) $this->address;
    }

    public function welcomeHasBeenSent()
    {
        return $this->welcome_sent;
    }

    public function hostPhoto(): string
    {
        if ($this->host->image) {
            makeImagePublic($this->host->image);

            return bunnyImage($this->host->image, [
                'height' => 100,
            ]);
        }

        return asset('svgs/user.svg');
    }

    public function hostAvatar(): ?string
    {
        if ($this->host->image) {
            return $this->host->image;
        }

        return null;
    }

    public function allAttendeeEmails(): string
    {
        return implode(', ', $this->attendees->pluck('email')->toArray());
    }

    public function newEloquentBuilder($query): EventBuilder
    {
        return new EventBuilder($query);
    }

    public function getLocationAttribute(): string
    {
        if (strtolower($this->country) === self::DEFAULT_COUNTRY || strtolower($this->country) === 'united states') {
            if ($this->city && $this->region) {
                return $this->city.', '.getStateAbbreviation($this->region);
            }
        }

        if (strtolower($this->country) === 'new zealand') {
            if ($this->city && $this->region) {
                return $this->city.', NZ';
            }
        }

        if (strtolower($this->country) === 'gb') {
            if ($this->city && $this->region) {
                return $this->city.', UK';
            }
        }

        return $this->city.', '.strtoupper($this->country);
    }

    public function getCityAttribute(): array|string
    {
        return str_replace(',', '', $this->attributes['city']);
    }
}
