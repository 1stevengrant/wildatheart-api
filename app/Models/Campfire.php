<?php

namespace App\Models;

use App\Builders\CampfireBuilder;
use App\Enums\CampfireFrequency;
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

class Campfire extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'frequency' => CampfireFrequency::class,
    ];

    protected $appends = ['url'];

    const STATUS_APPROVED = 'approved';

    const STATUS_PENDING = 'pending';

    const DEFAULT_COUNTRY = 'us';

    public function newEloquentBuilder($query): CampfireBuilder
    {
        return new CampfireBuilder($query);
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
        return route('view.fire', ['campfire' => $this]);
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
        return [
            'objectID' => $this->id,
            'id' => $this->id,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'name' => $this->name,
            'host' => $this->hostName,
            'location' => $this->location,
            'hostPhoto' => $this->hostAvatar(),
            'url' => $this->url,
            '_geoloc' => [
                'lat' => floatval($this->latitude),
                'lng' => floatval($this->longitude),
            ],
        ];
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
        return [
            'host' => $this->host_name,
            'hostFriendlyName' => $this->host->given_name,
            'location' => $this->location,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'hostPhoto' => $this->hostAvatar(),
            'url' => $this->url,
            'id' => $this->id,
            'hostId' => $this->host->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'gift_requested' => $this->hasRequestedGift(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->approved_at;
    }
}
