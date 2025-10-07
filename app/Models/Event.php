<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\EventSexEnum;
use App\Builders\EventBuilder;
use App\Enums\Event\FrequencyEnum;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    protected $with = ['eventType'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'deleted_at' => 'datetime',
            'legacy_drupal_location' => 'array',
            'longitude' => 'float',
            'latitude' => 'float',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'welcome_sent' => 'boolean',
            'welcome_sent_at' => 'datetime',
            'gift_requested' => 'boolean',
            'sex' => EventSexEnum::class,
            'view_count' => 'integer',
            'attendee_count' => 'integer',
            'is_private' => 'boolean',
            'frequency' => FrequencyEnum::class,
            'last_checkin_email_sent_at' => 'datetime',
            'last_checkin_reminder_sent_at' => 'datetime',
            'last_checkin_confirmed_at' => 'datetime',
            'auto_unpublished' => 'boolean',
            'is_featured' => 'boolean',
            'custom_landing_url' => 'string',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('events')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->fullname : 'System';

                return "Event has been $eventName by $userName";
            });
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($event) {
            $event->attendees()->delete();
        });
    }

    public function newEloquentBuilder($query): EventBuilder
    {
        return new EventBuilder($query);
    }

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const string FOR_MEN = 'men';

    public const string FOR_WOMEN = 'women';

    public const string DEFAULT_COUNTRY = 'us';

    public const string COUNT_TYPE_VIEW = 'view_count';

    public const string COUNT_TYPE_ATTENDEE = 'attendee_count';

    public const array MATERIAL_ORDER_ITEMS = [
        ['value' => '', 'label' => 'Select'],
        ['value' => '0', 'label' => 'None'],
        ['value' => '1 x 10-pack', 'label' => '1-10'],
        ['value' => '2 x 10-pack', 'label' => '11-20'],
        ['value' => '3 x 10-pack', 'label' => '21-30'],
        ['value' => '4 x 10-pack', 'label' => '31-40'],
        ['value' => '5 x 10-pack', 'label' => '41-50'],
        ['value' => '6 x 10-pack', 'label' => '51-60'],
        ['value' => '7 x 10-pack', 'label' => '61-70'],
        ['value' => '8 x 10-pack', 'label' => '71-80'],
        ['value' => '9 x 10-pack', 'label' => '81-90'],
        ['value' => '10 x 10-pack', 'label' => '91-100'],
        ['value' => '20 x 10-pack', 'label' => '100+'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    protected function location(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                // Handle case where country doesn't exist
                if (! $this->country) {
                    return $this->city ?: '';
                }

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
        );
    }

    protected function formattedDates(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (! $this->start_date) {
                    return '';
                }

                $startDate = $this->start_date instanceof Carbon
                    ? $this->start_date
                    : Carbon::parse($this->start_date);

                $startFormatted = $startDate->format('D, M j');

                if ($this->end_date) {
                    $endDate = $this->end_date instanceof Carbon
                        ? $this->end_date
                        : Carbon::parse($this->end_date);

                    if (! $startDate->isSameDay($endDate)) {
                        $endFormatted = $endDate->format('D, M j');

                        return "{$startFormatted} - {$endFormatted}";
                    }
                }

                return $startFormatted;
            }
        );
    }

    protected function city(): Attribute
    {
        return Attribute::make(
            get: function (string $value): string|array {
                return str_replace(',', '', $value);
            }
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return $this->user->email;
            }
        );
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return route('view.event', ['event' => $this]);
            }
        );
    }

    protected function adminUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return url(config('statamic.cp.route') . '/runway/event/' . $this->id);
            }
        );
    }

    protected function hostName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return ucwords($this->user->fullname);
            }
        );
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->eventType->name === EventType::FIRES) {
                    return $this->name . ' Fire';
                }
                if ($this->eventType->name === EventType::CAPTIVATING_FIRE) {
                    return $this->name . ' Captivating Fire';
                }

                return $this->eventType->name;
            },
        );
    }

    protected function stateCode(): Attribute
    {
        return Attribute::make(
            set: function ($value): string {
                return getStateAbbreviation(mb_strtoupper($value));
            }
        );
    }

    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: function ($value): ?string {
                return $value ? Carbon::parse($value)->format('Y-m-d') : null;
            }
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: function ($value): ?string {
                return $value ? Carbon::parse($value)->format('Y-m-d') : null;
            }
        );
    }

    protected function cardTitle(): Attribute
    {
        return Attribute::make(
            get: function () {
                $titleFormats = [
                    EventType::TYPE_FIRE => 'Monthly Gathering for',
                    EventType::TYPE_RETREAT => 'Multi-Day Retreat for',
                    'default' => '6-part Video Study for',
                ];

                $format = $titleFormats[$this->eventType->group_type] ?? $titleFormats['default'];
                $genderValue = ucfirst($this->sex->value);

                return "$format $genderValue";
            }
        );
    }

    protected function longitude(): Attribute
    {
        return Attribute::make(
            get: function ($value): float {
                return round($value, 2);
            }
        );
    }

    protected function latitude(): Attribute
    {
        return Attribute::make(
            get: function ($value): float {
                return round($value, 2);
            }
        );
    }

    protected function badgeDisplay(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $currentYear = now()->year;

                // Retreat: Show date range "May 16th - 19th 2025"
                if ($this->isRetreat()) {
                    if ($this->start_date && $this->end_date) {
                        $startDate = Carbon::parse($this->start_date);
                        $endDate = Carbon::parse($this->end_date);

                        // If same month and year, show condensed format
                        if ($startDate->month === $endDate->month && $startDate->year === $endDate->year) {
                            $endFormat = $endDate->year !== $currentYear ? 'jS Y' : 'jS';

                            return $startDate->format('M jS') . '—' . $endDate->format($endFormat);
                        }

                        // Different months or years
                        $endFormat = $endDate->year !== $currentYear ? 'M jS Y' : 'M jS';

                        return $startDate->format('M jS') . '—' . $endDate->format($endFormat);
                    }
                }

                // Experience: Show "Starts May 31st 2025"
                if ($this->isExperience()) {
                    if ($this->start_date) {
                        $startDate = Carbon::parse($this->start_date);
                        $format = $startDate->year !== $currentYear ? 'M jS Y' : 'M jS';

                        return 'Starts ' . $startDate->format($format);
                    }
                }

                // Fire: Logic depends on date and frequency
                if ($this->isFire()) {

                    if ($this->frequency) {

                        return ucwords($this->frequency->label());
                    }

                    return 'Monthly';
                }

                // Default: Just return the start date if available
                if ($this->start_date) {
                    $startDate = Carbon::parse($this->start_date);
                    $format = $startDate->year !== $currentYear ? 'F jS Y' : 'F jS';

                    return $startDate->format($format);
                }

                return '';
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return (bool) $this->approved_at;
    }

    public function hasRequestedGift(): bool
    {
        return (bool) $this->gift_requested;
    }

    public function isFire(): bool
    {
        return $this->eventType->group_type === EventType::TYPE_FIRE;
    }

    public function isRetreat(): bool
    {
        return $this->eventType->group_type === EventType::TYPE_RETREAT;
    }

    public function isExperience(): bool
    {
        return $this->eventType->group_type === EventType::TYPE_EXPERIENCE;
    }

    /*
    |--------------------------------------------------------------------------
    | Action Methods
    |--------------------------------------------------------------------------
    */

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
        // found at https://dashboard.wegive.com/data/tags/

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

    public function hasBeenAutoUnpublished(): bool
    {
        return (bool) $this->auto_unpublished;
    }
}
