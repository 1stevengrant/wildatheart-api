<?php

namespace App\Builders;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Builder;

class EventBuilder extends Builder
{
    public function public(): EventBuilder
    {
        return $this->where('is_private', false);
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
            EventType::WAH_RETREAT,
            EventType::CAPTIVATING_CORE_RETREAT,
            EventType::BECOMING_A_KING,
        ])->pluck('id');

        return $this->whereIn('event_type_id', $eventTypes);
    }

    public function wahRetreat(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::WAH_RETREAT,
        ])->pluck('id');

        return $this->whereIn('event_type_id', $eventTypes);
    }

    public function captivatingRetreat(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::CAPTIVATING_CORE_RETREAT,
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

    public function active(): EventBuilder
    {
        return $this->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '>=', now());
        });
    }

    public function inactive(): EventBuilder
    {
        return $this->where('start_date', '<', now());
    }

    public function user(int $userId): EventBuilder
    {
        return $this->where('user_id', $userId);
    }

    public function withinBoundingBox(
        float $swLatitude,
        float $swLongitude,
        float $neLatitude,
        float $neLongitude
    ): EventBuilder {
        $swLongitude = fmod(($swLongitude + 540), 360) - 180;
        $neLongitude = fmod(($neLongitude + 540), 360) - 180;

        if ($swLongitude > $neLongitude) {
            return $this->whereBetween('latitude', [$swLatitude, $neLatitude])
                ->where(function ($query) use ($swLongitude, $neLongitude) {
                    $query->where('longitude', '>=', $swLongitude)
                        ->orWhere('longitude', '<=', $neLongitude);
                });
        }

        return $this->whereBetween('latitude', [$swLatitude, $neLatitude])
            ->whereBetween('longitude', [$swLongitude, $neLongitude]);
    }

    public function notPrivate(): EventBuilder
    {
        return $this->where('is_private', false);
    }

    public function private(): EventBuilder
    {
        return $this->where('is_private', true);
    }

    public function approved(): EventBuilder
    {
        return $this->whereNotNull('approved_at');
    }

    public function betweenDates(?string $startDate = null, ?string $endDate = null): EventBuilder
    {
        if ($startDate && $endDate) {
            return $this->whereBetween('start_date', [$startDate, $endDate]);
        }

        if ($startDate) {
            return $this->where('start_date', '>=', $startDate);
        }

        if ($endDate) {
            return $this->where('start_date', '<=', $endDate);
        }

        return $this;
    }

    public function filterBySex(?string $sex): EventBuilder
    {
        if ($sex && in_array(strtolower($sex), ['men', 'women'])) {
            return $this->where('sex', strtolower($sex));
        }

        return $this;
    }

    public function filterByTypeGroup(?string $typeGroup): EventBuilder
    {
        if ($typeGroup) {
            return $this->where('type_group', $typeGroup);
        }

        return $this;
    }

    public function filterByEventTypes(?string $eventTypeIds): EventBuilder
    {
        if ($eventTypeIds) {
            $eventTypeIds = explode(',', $eventTypeIds);

            return $this->whereIn('event_type_id', $eventTypeIds);
        }

        return $this;
    }

    public function createdAfter(string $date): EventBuilder
    {
        return $this->where('created_at', '>=', $date);
    }

    public function startDateBetween(string $startDate, string $endDate): EventBuilder
    {
        return $this->whereBetween('start_date', [
            $startDate,
            $endDate,
        ]);
    }

    public function name(string $name, string $operator = '='): EventBuilder
    {
        return $this->where('name', $operator, "$name");
    }
}
