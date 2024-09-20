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

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($event) {
            if ($event->eventType) {
                $eventTypeName = $event->eventType->name;
                if (str_contains(strtolower($eventTypeName), 'retreat')) {
                    $event->type_group = 'retreat';
                }
                if (str_contains(strtolower($eventTypeName), 'experience')) {
                    $event->type_group = 'experience';
                }
                if (str_contains(strtolower($eventTypeName), 'fire')) {
                    $event->type_group = 'fire';
                }

            }
        });
    }

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

    public function hostFullName(): string
    {
        return ucwords($this->host->full_name);
    }

    public function getTypeGroupAttribute(): string
    {
        $eventTypeName = $this->eventType->name;
        if (str_contains(strtolower($eventTypeName), 'retreat')) {
            return 'retreat';
        }
        if (str_contains(strtolower($eventTypeName), 'experience')) {
            return 'experience';
        }
        return 'fire';
    }
}
