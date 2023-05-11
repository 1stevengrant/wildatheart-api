<?php

namespace App\Models;

use App\Builders\CampFireBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function spacesRemaining(): string
    {
        return "{$this->remaining} / {$this->attendee_limit} of spaces remaining";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }

    public function isHost(): bool
    {
        if (auth()->check() && auth()->user()->email === $this->user_email) {
            return true;
        }

        return false;
    }
}
