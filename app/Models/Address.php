<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends BaseModel
{
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
