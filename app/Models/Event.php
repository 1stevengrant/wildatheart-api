<?php

namespace App\Models;

use App\Enums\EventSexEnum;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use App\Builders\EventBuilder;
use App\Features\NewConnectUrl;
use App\Observers\EventObserver;
use App\Enums\Event\FrequencyEnum;
use Spatie\Activitylog\LogOptions;
use App\Features\NewEventLandingFeature;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\EventDateFormatters\FireFormatter;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use App\Support\EventDateFormatters\DefaultFormatter;
use App\Support\EventDateFormatters\RetreatFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Support\EventDateFormatters\ExperienceFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(EventObserver::class)]
class Event extends BaseModel
{
    use HasFactory, HasRunwayResource, HasUuids, LogsActivity, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    protected $with = ['eventType'];

    protected $hidden = [
        'host_invite_token',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'is_draft' => 'boolean',
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
            'new_card_heading' => 'string',
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

    /**
     * The event's name as people know it, for email subjects and copy. A fire's `name` is only the
     * host's prefix ("Nic's"), displayed as "Nic's Fire"; other events keep their stored name.
     */
    public function emailName(): string
    {
        $isFire = in_array($this->eventType?->name, [EventType::FIRES, EventType::CAPTIVATING_FIRE], true);

        return $isFire && filled($this->name) ? $this->display_name : (string) $this->name;
    }

    public function eventTypeSingular(): string
    {
        return Str::singular($this->eventType->name);
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function interestedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'event_user_interests',
            'event_id',
            'user_id'
        )
            ->withTimestamps()
            ->withPivot('archived_at');
    }

    public function managerInvitations(): HasMany
    {
        return $this->hasMany(EventManagerInvitation::class);
    }

    public function pendingManagerInvitations(): HasMany
    {
        // Global scope on EventManagerInvitation already filters by pending status
        return $this->hasMany(EventManagerInvitation::class);
    }

    public function ownershipTransfer(): HasOne
    {
        return $this->hasOne(EventOwnershipTransfer::class);
    }

    public function pendingOwnershipTransfer(): HasOne
    {
        // Global scope on EventOwnershipTransfer already filters by pending status
        return $this->hasOne(EventOwnershipTransfer::class);
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(EventSchedule::class);
    }

    public function scheduleDates(): HasMany
    {
        return $this->hasMany(EventScheduleDate::class)->orderBy('date');
    }

    /**
     * Dates from two days ago onwards, which covers "today" in every timezone.
     * Callers filter to the Fire's own local today.
     */
    public function recentScheduleDates(): HasMany
    {
        return $this->hasMany(EventScheduleDate::class)
            ->where('date', '>=', now()->subDays(2)->toDateString())
            ->orderBy('date');
    }

    public function scheduleNotifications(): HasMany
    {
        return $this->hasMany(EventScheduleNotification::class);
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
                if (! $this->city) {
                    return '';
                }

                if (! $this->country) {
                    return $this->city;
                }

                $country = mb_strtolower($this->country);

                if (in_array($country, [self::DEFAULT_COUNTRY, 'united states'], true) && $this->region) {
                    return $this->city . ', ' . (getStateAbbreviation($this->region) ?? $this->region);
                }

                if (in_array($country, ['gb', 'united kingdom'], true)) {
                    return $this->city . ', UK';
                }

                return $this->city . ', ' . (getCountryName($this->country) ?? ucfirst($this->country));
            }
        );
    }

    protected function formattedDates(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $year = now()->year;

                return match (true) {
                    $this->isRetreat() => RetreatFormatter::format($this, $year)
                        ?? DefaultFormatter::format($this, $year),
                    $this->isExperience() => ExperienceFormatter::format($this, $year)
                        ?? DefaultFormatter::format($this, $year),
                    $this->isFire() => FireFormatter::format($this),
                    default => DefaultFormatter::format($this, $year),
                };
            }
        );
    }

    protected function city(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => str_replace(',', '', $value ?? ''),
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
                if (Feature::active(NewEventLandingFeature::class)) {
                    return "/events/attend/$this->id";
                }

                return route('view.event', ['event' => $this]);
            }
        );
    }

    protected function attendUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => mb_rtrim(config('app.frontend_url'), '/') . "/events/attend/{$this->id}",
        );
    }

    protected function connectUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Feature::active(NewConnectUrl::class)
                ? "/events/connect/{$this->id}"
                : config('app.url') . "/connect/{$this->id}",
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

                if ($this->eventType->name === EventType::CAPTIVATING_CORE_RETREAT_1_DAY) {
                    $format = '1-Day Retreat for';
                }

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
                        // Single day retreat
                        if ($this->start_date->isSameDay($this->end_date)) {
                            $format = $this->start_date->year !== $currentYear ? 'M jS Y' : 'M jS';

                            return $this->start_date->format($format);
                        }

                        // If same month and year, show condensed format
                        if ($this->start_date->month === $this->end_date->month && $this->start_date->year === $this->end_date->year) {
                            $endFormat = $this->end_date->year !== $currentYear ? 'jS Y' : 'jS';

                            return $this->start_date->format('M jS') . '—' . $this->end_date->format($endFormat);
                        }

                        // Different months or years
                        $endFormat = $this->end_date->year !== $currentYear ? 'M jS Y' : 'M jS';

                        return $this->start_date->format('M jS') . '—' . $this->end_date->format($endFormat);
                    }
                }

                // Experience: Show "Starts May 31st 2025"
                if ($this->isExperience()) {
                    if ($this->start_date) {
                        $format = $this->start_date->year !== $currentYear ? 'M jS Y' : 'M jS';

                        return 'Starts ' . $this->start_date->format($format);
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
                    $format = $this->start_date->year !== $currentYear ? 'F jS Y' : 'F jS';

                    return $this->start_date->format($format);
                }

                return '';
            }
        );
    }

    protected function eventTypeCardDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->eventType->name) {
                EventType::FIRES, EventType::CAPTIVATING_FIRE => $this->name . ' Fire',
                EventType::WAH_EXPERIENCE => 'Restoring the heart of a man',
                EventType::WAH_RETREAT => 'Recover your wild heart',
                EventType::SURVIVAL_SCHOOL => 'Survival School',
                EventType::BECOMING_A_KING_EXPERIENCE => 'Becoming A King',
                EventType::BECOMING_A_KING => 'Becoming A King Retreat',
                EventType::CAPTIVATING_EXPERIENCE => 'Captivating Experience',
                EventType::CAPTIVATING_CORE_RETREAT => '4-Day Captivating Retreat',
                EventType::CAPTIVATING_CORE_RETREAT_1_DAY => '1-Day Captivating Retreat',
                default => $this->card_description ?? '',
            }
        );
    }

    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->isApproved(),
            set: fn (mixed $value, array $attributes): array => [
                'approved_at' => $value
                    ? ($attributes['approved_at'] ?? $this->fromDateTime(now()))
                    : null,
            ],
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

    public function isOwnedOrManagedBy(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->managers()->where('user_id', $user->id)->exists();
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

    public function hasBeenAutoUnpublished(): bool
    {
        return (bool) $this->auto_unpublished;
    }
}
