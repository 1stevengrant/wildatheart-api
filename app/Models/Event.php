<?php

namespace App\Models;

use App\Enums\EventSexEnum;
use App\Builders\EventBuilder;
use Spatie\Activitylog\LogOptions;
use App\Traits\Event\EventScopeTrait;
use Laravolt\Avatar\Facade as Avatar;
use App\Traits\Event\EventActionsTrait;
use App\Traits\Event\EventAttributesTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\Event\EventRelationshipsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends BaseModel
{
    use EventActionsTrait, EventAttributesTrait, EventRelationshipsTrait, EventScopeTrait, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($event) {
            if ($event->eventType) {
                $eventTypeName = $event->eventType->name;
                if (str_contains(mb_strtolower($eventTypeName), EventType::TYPE_RETREAT)) {
                    $event->type_group = EventType::TYPE_RETREAT;
                }
                if (str_contains(mb_strtolower($eventTypeName), EventType::TYPE_EXPERIENCE)) {
                    $event->type_group = EventType::TYPE_EXPERIENCE;
                }
                if (str_contains(mb_strtolower($eventTypeName), EventType::TYPE_FIRE)) {
                    $event->type_group = EventType::TYPE_FIRE;
                }

            }
        });

        static::deleting(function ($event) {
            $event->attendees()->delete();
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

            return $this->host->image;
        }

        return Avatar::create($this->host->full_name)->toBase64();
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
        if (mb_strtolower($this->country) === self::DEFAULT_COUNTRY || mb_strtolower($this->country) === 'united states') {
            if ($this->city && $this->region) {
                return $this->city.', '.getStateAbbreviation($this->region);
            }
        }

        if (mb_strtolower($this->country) === 'new zealand') {
            if ($this->city && $this->region) {
                return $this->city.', NZ';
            }
        }

        if (mb_strtolower($this->country) === 'gb') {
            if ($this->city && $this->region) {
                return $this->city.', UK';
            }
        }

        return $this->city.', '.mb_strtoupper($this->country);
    }

    public function getCityAttribute(): array|string
    {
        return str_replace(',', '', $this->attributes['city']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('events')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->full_name : 'System';

                return "Event has been {$eventName} by {$userName}";
            });
    }
}
