<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventManagerInvitation extends BaseModel
{
    use HasUlids;

    protected $fillable = [
        'event_id',
        'email',
        'invited_by_name',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function markAsAccepted(): void
    {
        $this->accepted_at = now();
        $this->save();
    }
}
