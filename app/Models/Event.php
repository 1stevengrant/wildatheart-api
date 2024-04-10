<?php

namespace App\Models;

use App\Enums\EventSexEnum;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'legacy_drupal_location' => 'array',
        'longitude' => 'float',
        'latitude' => 'float',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'welcome_sent' => 'boolean',
        'gift_requested' => 'boolean',
        'sex' => EventSexEnum::class,
    ];

    protected $appends = ['url', 'type_group', 'event_type_image', 'host_name', 'host_photo', 'location'];

    protected $hidden = ['user_id', 'host', 'eventType'];

    const FOR_MEN = 'men';

    const FOR_WOMEN = 'women';

    const DEFAULT_COUNTRY = 'us';

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
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

    public function getHostNameAttribute()
    {
        return $this->host->full_name;
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
        if ($this->isUsAndHasCity()) {
            return $this->formatLocationWithStateAbbreviation($this->city);
        }

        if ($this->isMissingCity()) {
            return $this->formatLocationWithStateAbbreviation($this->district);
        }

        if ($this->isUsAndHasRegion()) {
            return $this->formatLocationWithStateAbbreviation($this->city, $this->region);
        }

        return $this->defaultLocation();
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

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function scopeWithinDistance(
        $query,
        $latitude,
        $longitude,
        $radius = 10,
        $sex = null,
        $typeGroup = null,
        $types = null,
        $startDate = null,
        $endDate = null
    ) {
        $sex = $sex ?? '';
        $typeGroup = $typeGroup ?? '';
        $startDate = $startDate ?? '';
        $endDate = $endDate ?? '';

        $query
            ->select(
                'id',
                'name',
                'latitude',
                'longitude',
                'country',
                'sex',
                'event_type_id',
                'start_date',
                'end_date',
                'user_id',
                'country',
                'region',
                'city',
                'district',
                'state'
            )
            ->selectRaw(
                '(3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->whereNull('deleted_at')
            ->having('distance', '<=', $radius)
            ->orderBy('distance');

        if ($sex) {
            $query->where('sex', $sex);
        }

        if (! empty($types)) {
            $query->whereIn('event_type_id', explode(',', $types));
        }

        if ($startDate) {
            $query->whereRaw('(start_date >= ? OR start_date IS NULL)', [$startDate]);
        }

        if ($endDate) {
            $query->whereRaw('(end_date <= ? OR end_date IS NULL)', [$endDate]);
        }

        if ($typeGroup) {
            $query->whereHas('eventType', function ($query) use ($typeGroup) {
                $query->where('name', 'like', '%'.$typeGroup.'%');
            });
        }

        return $query;
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

    private function isUsAndHasCity(): bool
    {
        return strtolower($this->country) === self::DEFAULT_COUNTRY && $this->state !== '-' && ! empty($this->city);
    }

    private function isMissingCity(): bool
    {
        return ! $this->city || empty($this->city);
    }

    private function isUsAndHasRegion(): bool
    {
        return strtolower($this->country) === self::DEFAULT_COUNTRY && ! empty($this->region);
    }

    private function formatLocationWithStateAbbreviation($location, $state = null): string
    {
        $state = $state ?? $this->state;

        return $location.', '.getStateAbbreviation(strtoupper($state));
    }

    private function defaultLocation(): string
    {
        return $this->city.', '.strtoupper($this->country);
    }
}
