<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use App\Enums\Event\FrequencyEnum;
use App\Support\Schedule\ScheduleDateGenerator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventSchedule extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'frequency' => FrequencyEnum::class,
            'anchor_date' => 'immutable_date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function dates(): HasMany
    {
        return $this->hasMany(EventScheduleDate::class, 'event_id', 'event_id')->orderBy('date');
    }

    public function autoRenews(): bool
    {
        return $this->frequency->autoRenews();
    }

    public function localToday(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone)->startOfDay();
    }

    public function defaultStartTime(): string
    {
        return mb_substr($this->default_start_time, 0, 5);
    }

    public function defaultEndTime(): string
    {
        return mb_substr($this->default_end_time, 0, 5);
    }

    public function patternLabel(): ?string
    {
        if (! $this->anchor_date) {
            return null;
        }

        return ScheduleDateGenerator::patternLabel($this->frequency, $this->anchor_date);
    }
}
