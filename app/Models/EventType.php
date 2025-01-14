<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Builders\EventTypeBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventType extends BaseModel
{
    use HasFactory, HasRunwayResource, LogsActivity;

    protected $guarded = ['id'];

    const WAH_RETREAT = 'Wild at Heart Retreat';

    const CAPTIVATING_CORE_RETREAT = 'Captivating Retreat';

    const BECOMING_A_KING = 'Becoming A King Retreat';

    const WAH_EXPERIENCE = 'Wild at Heart Experience';

    const CAPTIVATING_EXPERIENCE = 'Captivating Experience';

    const BECOMING_A_KING_EXPERIENCE = 'Becoming A King Experience';

    const SURVIVAL_SCHOOL = 'Survival School';

    const FIRES = 'Fires';

    const CAPTIVATING_FIRE = 'Captivating Fires';

    const TYPE_RETREAT = 'retreat';

    const TYPE_EXPERIENCE = 'experience';

    const TYPE_FIRE = 'fire';

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($eventType) {
            $eventType->slug = Str::slug($eventType->name);
        });

        // Add debug logging to verify events are firing
        static::updated(function ($model) {
            \Log::info('EventType updated', [
                'id' => $model->id,
                'changes' => $model->getDirty(),
                'log_name' => 'event_types',
                'user' => auth()->user()?->full_name,
            ]);
        });

        static::created(function () {
            $cacheKeys = collect(['allEventTypes', 'experienceTypes', 'retreatTypes', 'fireTypes']);

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
            ->logAll() // Log all attributes since you're using $guarded
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('event_types')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->full_name : 'System';

                return "Event type has been {$eventName} by {$userName}";
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
}
