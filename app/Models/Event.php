<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\EventSexEnum;
use App\Builders\EventBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(\App\Observers\EventObserver::class)]
class Event extends BaseModel
{
    use HasFactory, HasRunwayResource, HasUuids, LogsActivity, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($event) {
            $event->attendees()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'deleted_at' => 'datetime',
            'legacy_drupal_location' => 'array',
            'longitude' => 'float',
            'latitude' => 'float',
            'start_date' => 'date',
            'end_date' => 'date',
            'welcome_sent' => 'boolean',
            'welcome_sent_at' => 'datetime',
            'gift_requested' => 'boolean',
            'sex' => EventSexEnum::class,
            'view_count' => 'integer',
            'attendee_count' => 'integer',
        ];
    }

    const FOR_MEN = 'men';

    const FOR_WOMEN = 'women';

    const DEFAULT_COUNTRY = 'us';

    public const COUNT_TYPE_VIEW = 'view_count';

    public const COUNT_TYPE_ATTENDEE = 'attendee_count';

    public function isApproved(): bool
    {
        return (bool) $this->approved_at;
    }

    public function hasRequestedGift(): bool
    {
        return (bool) $this->gift_requested;
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

    // action methods
    public function unpublish(): void
    {
        $this->approved_at = null;
        $this->save();
    }

    public function publish(): void
    {
        $this->approved_at = now();
        $this->save();
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getUrlAttribute(): string
    {
        return route('view.event', ['event' => $this]);
    }

    public function getAdminUrlAttribute(): string
    {
        return url(config('statamic.cp.route').'/runway/event/'.$this->id);
    }

    public function getHostNameAttribute()
    {
        return $this->user->full_name;
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
        $this->attributes['state'] = getStateAbbreviation(mb_strtoupper($value));
    }

    public function getStartDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getLongitudeAttribute(): float
    {
        return round($this->attributes['longitude'], 2);
    }

    public function getLatitudeAttribute(): float
    {
        return round($this->attributes['latitude'], 2);
    }

    // relationship methods
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
