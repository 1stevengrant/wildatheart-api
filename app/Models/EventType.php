<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends BaseModel
{
    const WAH_BASIC = 'Wild at Heart BASIC (retreat)';

    const CAPTIVATING_CORE_RETREAT = 'Captivating CORE (retreat)';

    const BECOMING_A_KING = 'Becoming A King (retreat)';

    const WAH_EXPERIENCE = 'Wild at Heart (experience)';

    const CAPTIVATING_EXPERIENCE = 'Captivating (experience)';

    const BECOMING_A_KING_EXPERIENCE = 'Becoming A King (experience)';

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
}
