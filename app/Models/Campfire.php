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
    use HasFactory, HasUuids, SoftDeletes, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'is_private' => 'boolean',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'frequency' => CampfireFrequency::class,
    ];

    protected $appends = ['url'];

    const STATUS_APPROVED = 'approved';

    const STATUS_PENDING = 'pending';

    public function newEloquentBuilder($query): CampfireBuilder
    {
        return new CampfireBuilder($query);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function getAttendeesCountAttribute(): int
    {
        return $this->attendees->count();
    }

    public function getRemainingAttribute(): int
    {
        return $this->attendee_limit - $this->attendees->count();
    }

    public function getAttendeeCountAttribute()
    {
        return $this->attendees->count();
    }

    public function spacesRemaining(): string
    {
        return "{$this->remaining} spaces remaining";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isHost(): bool
    {
        if (auth()->check() && auth()->user()->id === $this->user->id) {
            return true;
        }

        return false;
    }

    public function isApproved(): bool
    {
        if ($this->approved_at) {
            return true;
        }

        return false;
    }

    public function getCoordinatesAttribute(): string
    {
        return $this->longitude.', '.$this->latitude;
    }

    public function isPast(): bool
    {
        if ($this->date < now()) {
            return true;
        }

        return false;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getUrlAttribute(): string
    {
        return route('view.campfire', ['campfire' => $this]);
    }

    public function isPrivate(): bool
    {
        if ($this->is_private) {
            return true;
        }

        return false;
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

    public function isOverCapacity(): bool
    {
        if ($this->attendee_limit < $this->attendees->count()) {
            return true;
        }

        return false;
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

    public function getLocationAttribute(): string
    {
        if ($this->country === 'US') {
            return $this->city.', '.getStateAbbreviation($this->state);
        }

        return $this->city;
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function hasRequestedGift(): bool
    {
        if ($this->gift_requested) {
            return true;
        }

        return false;
    }

    public function hasAddress(): bool
    {
        if ($this->address) {
            return true;
        }

        return false;
    }

    public function indoorOrOutdoor(): string
    {
        if ($this->is_indoor) {
            return 'Indoor';
        }

        return 'Outdoor';
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
        return config('scout.prefix').'fires';
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
            'hostPhoto' => $this->hostPhoto(),
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
}
