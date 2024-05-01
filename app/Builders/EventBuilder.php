<?php

namespace App\Builders;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Builder;

class EventBuilder extends Builder
{
    public function public(): EventBuilder
    {
        return $this->whereNotNull('approved_at');
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

    public function forWomen(): EventBuilder
    {
        return $this->whereSex('women');
    }

    public function forMen(): EventBuilder
    {
        return $this->whereSex('men');

    }
}
