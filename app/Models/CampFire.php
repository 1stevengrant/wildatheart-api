<?php

namespace App\Models;

use App\Builders\CampFireBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampFire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function newEloquentBuilder($query): CampFireBuilder
    {
        return new CampFireBuilder($query);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function getRemainingAttribute(): int
    {
        return $this->attendee_limit - $this->attendees->count();
    }
}
