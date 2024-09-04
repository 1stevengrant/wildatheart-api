<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class EventType extends Model
{
    use HasRunwayResource;

    protected $guarded = ['id'];

    const WAH_BASIC = 'Wild at Heart BASIC (retreat)';

    const CAPTIVATING_CORE_RETREAT = 'Captivating CORE (retreat)';

    const BECOMING_A_KING = 'Becoming A King (retreat)';

    const WAH_EXPERIENCE = 'Wild at Heart (experience)';

    const CAPTIVATING_EXPERIENCE = 'Captivating (experience)';

    const BECOMING_A_KING_EXPERIENCE = 'Becoming A King (experience)';

    const SURVIVAL_SCHOOL = 'Survival School';

    const FIRES = 'Fires';

    protected $casts = [
        'key_details' => 'array',
        'photos' => 'array',
        'event_faqs' => 'array',
        'event_schedule' => 'array',
        'is_active' => 'boolean',
        'event_guide_pages' => 'array',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', '=', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', '=', false);
    }
}
