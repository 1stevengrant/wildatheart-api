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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

    public function newEloquentBuilder($query): EventBuilder
    {
        return new EventBuilder($query);
    }
}
