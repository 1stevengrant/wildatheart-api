<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getFullnameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campfire(): BelongsTo
    {
        return $this->belongsTo(Campfire::class);
    }

    public function hasUser(): bool
    {
        return $this->user_id !== null;
    }
}
