<?php

namespace App\Models;

use App\Enums\Event\ScheduleNotificationKind;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventScheduleNotification extends BaseModel
{
    protected function casts(): array
    {
        return [
            'kind' => ScheduleNotificationKind::class,
            'last_date' => 'immutable_date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
