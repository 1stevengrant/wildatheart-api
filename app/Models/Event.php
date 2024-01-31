<?php

namespace App\Models;

use App\Builders\EventBuilder;
use App\Enums\EventFrequency;
use App\Transformers\EventInertiaTransformer;
use App\Transformers\Search\EventSearchTransformer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $guarded = ['id'];

    protected $with = ['eventType'];

    protected $casts = [
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'frequency' => EventFrequency::class,
        'legacy_drupal_location' => 'array',
    ];

    protected $appends = ['url'];

    const FOR_MEN = 'm';

    const FOR_WOMEN = 'f';

    const DEFAULT_COUNTRY = 'us';

    public function newEloquentBuilder($query): EventBuilder
    {
        return new EventBuilder($query);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function getAttendeeCountAttribute(): int
    {
        return $this->attendees->count();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isHost(): bool
    {
        return auth()->check() && $this->user->id === auth()->user()->id;
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved_at;
    }

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

    public function bookShipments(): HasMany
    {
        return $this->hasMany(BookShipment::class);
    }

    public function getHostNameAttribute()
    {
        return $this->host->full_name;
    }

    public function getBookShipmentsCountAttribute()
    {
        return $this->bookShipments->count();
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function getLocationAttribute(): string
    {
        if (strtolower($this->country) === self::DEFAULT_COUNTRY && $this->state && $this->state !== '-' && ! empty($this->city)) {
            return $this->city.', '.getStateAbbreviation($this->state);
        }

        if (! $this->city or empty($this->city)) {
            return $this->district.', '.getStateAbbreviation($this->state);
        }

        if (strtolower($this->country) === self::DEFAULT_COUNTRY && ! empty($this->region)) {
            return $this->city.', '.getStateAbbreviation($this->region);
        }

        return $this->city.', '.strtoupper($this->country);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
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

    public function getAdminUrlAttribute(): \Illuminate\Foundation\Application|string|UrlGenerator|Application
    {
        return url('manager/resources/camp-fires/'.$this->id);
    }

    public function allAttendeeEmails(): string
    {
        return implode(', ', $this->attendees->pluck('email')->toArray());
    }

    public function searchableAs(): string
    {
        return config('scout.prefix').'wah_local';
    }

    public function toSearchableArray(): array
    {
        return $this->transform($this->toArray(), [
            EventSearchTransformer::class,
        ]);
    }

    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    public function getScoutKeyName(): string
    {
        return 'id';
    }

    public function inertiaFilter(): array
    {
        return $this->transform($this->toArray(), [
            EventInertiaTransformer::class,
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        if ($this->eventType->name === EventType::FIRES) {
            return (bool) $this->approved_at;
        }

        // all other event types
        if ($this->approved_at !== null && ($this->start_date !== null && $this->start_date->isFuture())) {
            return true;
        }

        return false;
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
