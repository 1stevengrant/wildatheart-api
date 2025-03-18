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

    protected $with = ['eventType'];

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
            'is_private' => 'boolean',
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
                return $this->city . ', ' . getStateAbbreviation($this->region);
            }
        }

        if (mb_strtolower($this->country) === 'new zealand') {
            if ($this->city && $this->region) {
                return $this->city . ', NZ';
            }
        }

        if (mb_strtolower($this->country) === 'gb') {
            if ($this->city && $this->region) {
                return $this->city . ', UK';
            }
        }

        return $this->city . ', ' . mb_strtoupper($this->country);
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
                $userName = $user ? $user->fullname : 'System';

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
        return url(config('statamic.cp.route') . '/runway/event/' . $this->id);
    }

    public function getHostNameAttribute()
    {
        return $this->user->fullname;
    }

    public function getTitleAttribute(): ?string
    {
        if ($this->eventType->name === EventType::FIRES) {
            return $this->name . ' Fire';
        }
        if ($this->eventType->name === EventType::CAPTIVATING_FIRE) {
            return $this->name . ' Captivating Fire';
        }

        return $this->eventType->name;
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

    public const MATERIAL_ORDER_ITEMS = [
        ['value' => '', 'label' => 'Select'],
        ['value' => '0', 'label' => 'None'],
        ['value' => '10', 'label' => '1-10'],
        ['value' => '20', 'label' => '11-20'],
        ['value' => '30', 'label' => '21-30'],
        ['value' => '40', 'label' => '31-40'],
        ['value' => '50', 'label' => '41-50'],
        ['value' => '60', 'label' => '51-60'],
        ['value' => '70', 'label' => '61-70'],
        ['value' => '80', 'label' => '71-80'],
        ['value' => '90', 'label' => '81-90'],
        ['value' => '100', 'label' => '91-100'],
        ['value' => '200', 'label' => '100+'],
    ];

    public function togglePrivacy(): void
    {
        $this->is_private = ! $this->is_private;
        $this->save();
    }

    public function toggleApprovedAt(): void
    {
        $this->approved_at = $this->approved_at ? null : now();
        $this->save();
    }

    public function getWeGiveTagAttribute(): ?int
    {
        // these magic numbers are the tag ids in WeGive environments

        if (app()->isProduction()) {
            $eventTypeMappings = [
                EventType::FIRES => 239,
                EventType::CAPTIVATING_FIRE => 240,
                EventType::WAH_EXPERIENCE => 241,
                EventType::WAH_RETREAT => 242,
                EventType::SURVIVAL_SCHOOL => 243,
                EventType::BECOMING_A_KING_EXPERIENCE => 244,
                EventType::BECOMING_A_KING => 245,
                EventType::CAPTIVATING_EXPERIENCE => 246,
                EventType::CAPTIVATING_CORE_RETREAT => 247,
            ];
        } else {

            $eventTypeMappings = [
                EventType::FIRES => 281,
                EventType::WAH_EXPERIENCE => 282,
                EventType::WAH_RETREAT => 283,
                EventType::SURVIVAL_SCHOOL => 284,
                EventType::BECOMING_A_KING_EXPERIENCE => 285,
                EventType::BECOMING_A_KING => 286,
                EventType::CAPTIVATING_EXPERIENCE => 287,
                EventType::CAPTIVATING_CORE_RETREAT => 288,
                EventType::CAPTIVATING_FIRE => 289,
            ];
        }

        return $eventTypeMappings[$this->eventType->name] ?? null;
    }
}
