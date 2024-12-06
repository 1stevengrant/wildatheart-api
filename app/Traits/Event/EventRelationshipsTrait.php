<?php

namespace App\Traits\Event;

use App\Models\User;
use App\Models\Address;
use App\Models\Attendee;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait EventRelationshipsTrait
{
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
