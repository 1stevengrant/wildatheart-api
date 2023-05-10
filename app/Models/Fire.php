<?php

namespace App\Models;

use App\Builders\FireBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function newEloquentBuilder($query): FireBuilder
    {
        return new FireBuilder($query);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function getRemainingAttribute(): int
    {
        return $this->attendee_limit - $this->attendees->count();
    }

    public function getDateAttribute($value): string
    {
        return $this->asDateTime($value)->format('F j, Y');
    }
}
