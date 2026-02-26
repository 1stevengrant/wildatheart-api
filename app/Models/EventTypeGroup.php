<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class EventTypeGroup extends BaseModel
{
    use HasFactory, HasRunwayResource;

    protected $guarded = ['id'];

    public function eventTypes(): HasMany
    {
        return $this->hasMany(EventType::class, 'type_id');
    }
}
