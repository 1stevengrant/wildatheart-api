<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Builders\EventTypeBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventType extends BaseModel
{
    use HasFactory, HasRunwayResource, LogsActivity;

    protected $guarded = ['id'];

    public const string WAH_RETREAT = 'Wild at Heart Retreat';

    public const string CAPTIVATING_CORE_RETREAT = 'Captivating Retreat';

    public const string BECOMING_A_KING = 'Becoming A King Retreat';

    public const string WAH_EXPERIENCE = 'Wild at Heart Experience';

    public const string CAPTIVATING_EXPERIENCE = 'Captivating Experience';

    public const string BECOMING_A_KING_EXPERIENCE = 'Becoming A King Experience';

    public const string SURVIVAL_SCHOOL = 'Survival School';

    public const string FIRES = 'Fires';

    public const string CAPTIVATING_FIRE = 'Captivating Fires';

    public const string TYPE_RETREAT = 'retreat';

    public const string TYPE_EXPERIENCE = 'experience';

    public const string TYPE_FIRE = 'fire';

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($eventType) {
            $eventType->slug = Str::slug($eventType->name);
        });

        static::created(function () {
            $cacheKeys = collect(['allEventTypes', 'experienceTypes', 'retreatTypes', 'fireTypes', 'eventTypesGroupedByType']);

            $cacheKeys->each(function ($key) {
                cache()->forget($key);
            });

        });
    }

    protected function casts(): array
    {
        return [
            'key_details' => 'array',
            'photos' => 'array',
            'event_faqs' => 'array',
            'event_schedule' => 'array',
            'is_active' => 'boolean',
            'event_guide_pages' => 'array',
            'action_buttons' => 'array',
            'new_content' => 'array',
            'new_image' => 'string',
            'summary' => 'string',
            'summary_link' => 'string',
            'type_id' => 'integer',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventTypeGroup::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function newEloquentBuilder($query): EventTypeBuilder
    {
        return new EventTypeBuilder($query);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('event_types')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->fullname : 'System';

                return "Event type has been $eventName by $userName";
            });
    }

    public function hasZapier(): bool
    {
        return ! empty($this->zapier_webhook_url);
    }

    public function hasMaterialsOrder(): bool
    {
        return $this->allow_materials_order;
    }

    protected function wegiveTag(): Attribute
    {
        return Attribute::make(
            get: function (): ?int {
                // these magic numbers are the tag ids in WeGive environments
                // found at https://dashboard.wegive.com/data/tags/

                if (app()->isProduction()) {
                    $eventTypeMappings = [
                        self::FIRES => 239,
                        self::CAPTIVATING_FIRE => 240,
                        self::WAH_EXPERIENCE => 241,
                        self::WAH_RETREAT => 242,
                        self::SURVIVAL_SCHOOL => 243,
                        self::BECOMING_A_KING_EXPERIENCE => 244,
                        self::BECOMING_A_KING => 245,
                        self::CAPTIVATING_EXPERIENCE => 246,
                        self::CAPTIVATING_CORE_RETREAT => 247,
                    ];
                } else {
                    $eventTypeMappings = [
                        self::FIRES => 281,
                        self::WAH_EXPERIENCE => 282,
                        self::WAH_RETREAT => 283,
                        self::SURVIVAL_SCHOOL => 284,
                        self::BECOMING_A_KING_EXPERIENCE => 285,
                        self::BECOMING_A_KING => 286,
                        self::CAPTIVATING_EXPERIENCE => 287,
                        self::CAPTIVATING_CORE_RETREAT => 288,
                        self::CAPTIVATING_FIRE => 289,
                    ];
                }

                return $eventTypeMappings[$this->name] ?? null;
            }
        );
    }

    protected function camelCaseName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Str::camel($this->name)
        );
    }
}
