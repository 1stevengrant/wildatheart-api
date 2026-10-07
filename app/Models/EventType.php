<?php

namespace App\Models;

use App\Enums\EventSexEnum;
use Illuminate\Support\Str;
use App\Enums\EventTypeEnum;
use App\Builders\EventTypeBuilder;
use Spatie\Activitylog\LogOptions;
use App\Observers\EventTypeObserver;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(EventTypeObserver::class)]
class EventType extends BaseModel
{
    use HasFactory, HasRunwayResource, LogsActivity;

    public const string WAH_RETREAT = 'Wild at Heart Retreat';

    public const string CAPTIVATING_CORE_RETREAT = '4-Day Captivating Retreat';

    public const string CAPTIVATING_CORE_RETREAT_1_DAY = '1-Day Captivating Retreat';

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

    public const int MAX_FIRE_NAME_LENGTH = 50;

    public const int MAX_EVENT_NAME_LENGTH = 255;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($eventType) {
            $eventType->slug = Str::slug($eventType->name);
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
            'welcome_nav_text_override' => 'string',
            'before_event_nav_text_override' => 'string',
            'schedule_nav_text_override' => 'string',
            'faq_nav_text_override' => 'string',
            'page_topics' => 'array',
            'page_hero_title' => 'array',
            'type' => EventTypeEnum::class,
            'sex' => EventSexEnum::class,
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
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

    /**
     * The longest name a host may give an event of this type. Fire names are shown as headings
     * ("Nic's Fire"), so they're kept short; other types are named after their location.
     */
    public function maxEventNameLength(): int
    {
        return $this->group_type === self::TYPE_FIRE ? self::MAX_FIRE_NAME_LENGTH : self::MAX_EVENT_NAME_LENGTH;
    }

    /**
     * Whether the host app asks hosts how many items to ship. Fires always ship a single kit,
     * as the legacy gift flow does, so they skip the count.
     */
    public function requiresMaterialOrderQuantity(): bool
    {
        return $this->hasMaterialsOrder() && $this->group_type !== self::TYPE_FIRE;
    }

    /**
     * The quantity to send with the host app's materials order: the host's choice, or one kit for fires.
     */
    public function materialOrderQuantity(?string $requestedQuantity): ?string
    {
        if ($this->hasMaterialsOrder() && ! $this->requiresMaterialOrderQuantity()) {
            return '1';
        }

        return $requestedQuantity;
    }

    protected function wegiveTag(): Attribute
    {
        return Attribute::make(
            get: function (): ?int {
                $environment = app()->isProduction() ? 'production' : 'staging';
                $mappings = config("services.wegive.event_type_tags.{$environment}", []);

                return $mappings[$this->name] ?? null;
            }
        );
    }

    protected function camelCaseName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Str::camel($this->name)
        );
    }

    /**
     * The V1 API description as plain text, for places that can't render HTML
     * such as link previews and event cards.
     */
    protected function pageDescriptionText(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $html = str_replace(['</p>', '</h2>', '</h3>', '</li>', '<br>'], ' ', (string) $this->page_description);
                $text = Str::of(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5))->squish()->toString();

                return $text === '' ? null : $text;
            }
        );
    }
}
