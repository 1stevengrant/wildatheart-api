<?php

namespace App\Builders;

use App\Models\User;
use App\Models\EventType;
use App\Enums\EventSexEnum;
use Illuminate\Database\Eloquent\Builder;

class EventBuilder extends Builder
{
    /** @var array<string, int> */
    private static array $eventTypeIdCache = [];

    private static function eventTypeId(string $name): int
    {
        return self::$eventTypeIdCache[$name] ??= EventType::where('name', $name)->first()->id;
    }

    public function fires(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::FIRES));
    }

    public function becomingAKingRetreats(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::BECOMING_A_KING));
    }

    public function becomingAKingExperiences(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::BECOMING_A_KING_EXPERIENCE));
    }

    public function wahRetreat(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::WAH_RETREAT));
    }

    public function wahExperience(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::WAH_EXPERIENCE));
    }

    public function captivatingRetreat(): EventBuilder
    {
        $eventTypeIds = [
            self::eventTypeId(EventType::CAPTIVATING_CORE_RETREAT),
            self::eventTypeId(EventType::CAPTIVATING_CORE_RETREAT_1_DAY),
        ];

        return $this->whereIn('event_type_id', $eventTypeIds);
    }

    public function captivatingFire(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::CAPTIVATING_FIRE));
    }

    public function captivatingExperience(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::CAPTIVATING_EXPERIENCE));
    }

    public function survivalSchool(): EventBuilder
    {
        return $this->where('event_type_id', self::eventTypeId(EventType::SURVIVAL_SCHOOL));
    }

    public function retreats(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::WAH_RETREAT,
            EventType::CAPTIVATING_CORE_RETREAT,
            EventType::CAPTIVATING_CORE_RETREAT_1_DAY,
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

    public function allFires(): EventBuilder
    {
        $eventTypes = EventType::whereIn('name', [
            EventType::FIRES,
            EventType::CAPTIVATING_FIRE,
        ])->pluck('id');

        return $this->whereIn('event_type_id', $eventTypes);
    }

    // General Properties

    public function public(): EventBuilder
    {
        return $this->where('is_private', false);
    }

    public function private(): EventBuilder
    {
        return $this->where('is_private', true);
    }

    public function published(): EventBuilder
    {
        return $this->whereNotNull('approved_at');
    }

    public function hasEventType(): EventBuilder
    {
        return $this->whereNotNull('event_type_id');
    }

    public function unpublished(): EventBuilder
    {
        return $this->whereNull('approved_at');
    }

    public function upcoming(): EventBuilder
    {
        return $this->where('start_date', '>=', now());
    }

    public function startedInTheLastYear(): EventBuilder
    {
        return $this->whereBetween('start_date', [now()->subYear(), now()]);
    }

    public function inactiveForDays(int $days): EventBuilder
    {
        return $this->where(function ($query) use ($days) {
            $query->where('updated_at', '<', now()->subDays($days))
                ->orWhereNull('updated_at');
        });
    }

    public function lastMonthlyEmailSentAgo(int $days, string $operator = '<'): EventBuilder
    {
        return $this->where('last_checkin_email_sent_at', $operator, now()->subDays($days));
    }

    public function lastApprovedAt(int $days, string $operator = '>='): EventBuilder
    {
        return $this->where('approved_at', $operator, now()->subDays($days));
    }

    public function active(): EventBuilder
    {
        $alwaysActiveWithStartDate = [
            EventType::FIRES,
            EventType::CAPTIVATING_FIRE,
        ];

        return $this->where(function ($query) use ($alwaysActiveWithStartDate) {
            $query->whereNull('start_date')
                ->orWhere(function ($subQuery) use ($alwaysActiveWithStartDate) {
                    // For special event types: active if start_date exists
                    $subQuery->whereIn('event_type_id', function ($eventTypeQuery) use ($alwaysActiveWithStartDate) {
                        $eventTypeQuery->select('id')
                            ->from('event_types')
                            ->whereIn('name', $alwaysActiveWithStartDate);
                    })
                        ->whereNotNull('start_date');
                })
                ->orWhere(function ($subQuery) use ($alwaysActiveWithStartDate) {
                    // For normal event types: active if start_date is in the future
                    $subQuery->whereNotIn('event_type_id', function ($eventTypeQuery) use ($alwaysActiveWithStartDate) {
                        $eventTypeQuery->select('id')
                            ->from('event_types')
                            ->whereIn('name', $alwaysActiveWithStartDate);
                    })
                        ->where('start_date', '>=', now());
                });
        }
        );
    }

    public function inactive(): EventBuilder
    {
        return $this->where('start_date', '<', now());
    }

    public function belongsToUser(int $userId): EventBuilder
    {
        return $this->where('user_id', $userId);
    }

    public function controllableByUser(User $user): EventBuilder
    {
        return $this->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('managers', fn ($q) => $q->where('user_id', $user->id));
        });
    }

    public function withinBoundingBox(
        float $swLatitude,
        float $swLongitude,
        float $neLatitude,
        float $neLongitude
    ): EventBuilder {
        // A span >= 360° covers the whole world; skip longitude filtering entirely.
        // Otherwise the wrap step below would collapse the box (e.g. 180 → -180).
        if (abs($neLongitude - $swLongitude) >= 360) {
            return $this->whereBetween('latitude', [$swLatitude, $neLatitude]);
        }

        $swLongitude = $this->wrapLongitude($swLongitude);
        $neLongitude = $this->wrapLongitude($neLongitude);

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

    private function wrapLongitude(float $longitude): float
    {
        $longitude = fmod($longitude, 360);

        if ($longitude > 180) {
            return $longitude - 360;
        }

        if ($longitude < -180) {
            return $longitude + 360;
        }

        return $longitude;
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
        $sexEnum = $sex ? EventSexEnum::tryFrom(mb_strtolower($sex)) : null;

        if ($sexEnum) {
            return $this->where('sex', $sexEnum);
        }

        return $this;
    }

    public function filterByTypeGroup(?string $typeGroup): EventBuilder
    {
        if ($typeGroup) {
            return $this->whereRelation('eventtype', 'group_type', $typeGroup);
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

    public function beginsAfter(string $date): EventBuilder
    {
        return $this->where('start_date', '>', $date);
    }

    public function inCountry(array $countries): EventBuilder
    {
        return $this->where(function ($query) use ($countries) {
            foreach ($countries as $country) {
                $query->orWhere('country', 'like', "%$country%");
            }
        });
    }

    public function inCity(string $city): EventBuilder
    {
        return $this->where(function ($query) use ($city) {
            $query->where('city', 'like', "%$city%");
        });
    }

    public function notAutoUnpublished(): EventBuilder
    {
        return $this->where('auto_unpublished', false);
    }

    public function lastCheckinNotConfirmed(): EventBuilder
    {
        return $this->whereNull('last_checkin_confirmed_at');
    }

    public function lastCheckinReminderNotSent(): EventBuilder
    {
        return $this->whereNull('last_checkin_reminder_sent_at');
    }

    public function lastCheckinReminderSent(): EventBuilder
    {
        return $this->whereNotNull('last_checkin_reminder_sent_at');
    }

    private function haversineFormula(): string
    {
        return '(3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';
    }

    /**
     * Filter events within a radius of a geographic point using Haversine formula.
     * Adds a calculated 'distance' column (in miles) to the results.
     */
    public function withinRadius(float $lat, float $lng, float $radiusMiles): EventBuilder
    {
        $haversine = $this->haversineFormula();

        return $this->select('events.*')
            ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("$haversine <= ?", [$lat, $lng, $lat, $radiusMiles])
            ->orderBy('distance');
    }

    /**
     * Order events by distance from a geographic point and limit the results.
     */
    public function nearestTo(float $lat, float $lng, int $limit): EventBuilder
    {
        $haversine = $this->haversineFormula();

        return $this->select('events.*')
            ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance')
            ->limit($limit);
    }

    /**
     * Filter events within a ring (donut) between minRadius and maxRadius miles.
     * Used for "load more" incremental radius expansion.
     */
    public function withinRing(float $lat, float $lng, float $minRadius, float $maxRadius): EventBuilder
    {
        $haversine = $this->haversineFormula();

        return $this->select('events.*')
            ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("$haversine > ?", [$lat, $lng, $lat, $minRadius])
            ->whereRaw("$haversine <= ?", [$lat, $lng, $lat, $maxRadius])
            ->orderBy('distance');
    }
}
