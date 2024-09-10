<?php

namespace App\Models;

use App\Builders\EventTypeBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class EventType extends BaseModel
{
    use HasFactory, HasRunwayResource;

    protected $guarded = ['id'];

    const WAH_BASIC = 'Wild at Heart Retreat';

    const CAPTIVATING_CORE_RETREAT = 'Captivating Retreat';

    const BECOMING_A_KING = 'Becoming A King Retreat';

    const WAH_EXPERIENCE = 'Wild at Heart Experience';

    const CAPTIVATING_EXPERIENCE = 'Captivating Experience';

    const BECOMING_A_KING_EXPERIENCE = 'Becoming A King Experience';

    const SURVIVAL_SCHOOL = 'Survival School';

    const FIRES = 'Fires';

    const TYPE_RETREAT = 'retreat';

    const TYPE_EXPERIENCE = 'experience';

    const TYPE_FIRE = 'fire';

    protected $casts = [
        'key_details' => 'array',
        'photos' => 'array',
        'event_faqs' => 'array',
        'event_schedule' => 'array',
        'is_active' => 'boolean',
        'event_guide_pages' => 'array',
        'action_buttons' => 'array',
        'new_content' => 'array',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($eventType) {
            $eventType->slug = \Str::slug($eventType->name);
        });
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
}
