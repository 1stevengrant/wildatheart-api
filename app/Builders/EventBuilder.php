<?php

namespace App\Builders;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Builder;

class EventBuilder extends Builder
{
    public function public(): EventBuilder
    {
        return $this->where('approved_at', '!=', null);
    }

    public function published(): EventBuilder
    {
        return $this->whereNotNull('approved_at');
    }

    public function unpublished(): EventBuilder
    {
        return $this->whereNull('approved_at');
    }

    public function fires(): EventBuilder
    {
        return $this->where('event_type_id', EventType::where('name', EventType::FIRES)->first()->id);
    }

    public function retreats(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::WAH_BASIC,
            EventType::CAPTIVATING_CORE_RETREAT,
            EventType::BECOMING_A_KING,
        ])->pluck('id');

        return $this->whereIn('event_type_id', $eventTypes);
    }

    public function experiences(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::WAH_EXPERIENCE,
            EventType::CAPTIVATING_EXPERIENCE,
            EventType::BECOMING_A_KING_EXPERIENCE,
        ])->pluck('id');

        return $this->whereIn('event_type_id', $eventTypes);
    }

    public function upcoming(): EventBuilder
    {
        return $this->where('start_date', '>=', now());
    }

    public function inTheLastYear(): EventBuilder
    {
        return $this->whereBetween('start_date', [now()->subYear(), now()]);
    }

    public function updatedAgo(int $days): EventBuilder
    {
        return $this->where('updated_at', '<', now()->subDays($days));
    }

    public function lastMonthlyEmailSentAgo(int $days, string $operator = '<'): EventBuilder
    {
        return $this->where('last_monthly_email_sent_at', $operator, now()->subDays($days));
    }

    public function lastApprovedAt(int $days, string $operator = '>='): EventBuilder
    {
        return $this->where('approved_at', $operator, now()->subDays($days));
    }
}
