<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventScheduleDate extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hasCustomTimes(): bool
    {
        return $this->start_time !== null;
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast();
    }

    public function localStartsAt(string $timezone): CarbonImmutable
    {
        return $this->starts_at->setTimezone($timezone);
    }

    public function localEndsAt(string $timezone): CarbonImmutable
    {
        return $this->ends_at->setTimezone($timezone);
    }
}
