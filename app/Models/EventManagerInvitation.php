<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventManagerInvitation extends BaseModel
{
    use HasUlids;

    protected $fillable = [
        'event_id',
        'email',
        'invited_by_user_id',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('pending', function (Builder $builder) {
            $builder->whereNull('accepted_at');
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function markAsAccepted(): void
    {
        $this->accepted_at = now();
        $this->save();
    }
}
