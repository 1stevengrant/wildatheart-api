<?php

namespace App\Models;

use App\Builders\CampfireBuilder;
use App\Enums\CampfireFrequency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
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

    public function title(): ?string
    {
        if ($this->name) {
            return $this->name.' Fire';
        }

        return null;
    }

    public function location(): string
    {
        return $this->city.', '.getStateAbbreviation($this->state);
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

    public function indoorOrOutdoor(): string
    {
        if ($this->is_indoor) {
            return 'Indoor';
        }

        return 'Outdoor';
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->title(),
            'organization' => $this->organization,
            'city' => $this->city,
            'state' => $this->state,
            'state_code' => $this->state_code,
            'mapbox_place_name' => $this->mapbox_place_name,
            'country' => $this->country,
            'zip' => $this->zip,
        ];
    }

    public function welcomeHasBeenSent()
    {
        return $this->welcome_sent;
    }

    public function hostPhoto(): string
    {
        if (! app()->environment('local')) {
            if ($this->image) {
                $path = $this->image;
                $file = Storage::get($path);
                $type = Storage::mimeType($path);
                $response = Response::make($file, 200);
                $response->header('Content-Type', $type);

                return $response;
            }

            return gravatar($this->host->email);
        }

        return $this->host->image ? asset($this->host->image) : gravatar($this->host->email);

    }
}
