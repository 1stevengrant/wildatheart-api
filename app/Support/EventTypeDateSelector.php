<?php

namespace App\Support;

use App\Models\EventType;

class EventTypeDateSelector
{
    public const string KIND_RECURRING = 'recurring';

    public const string KIND_SINGLE_START_DATE = 'single_start_date';

    public const string KIND_FIXED_DURATION_START_DATE = 'fixed_duration_start_date';

    public static function kind(?string $groupType): string
    {
        return match ($groupType) {
            EventType::TYPE_FIRE => self::KIND_RECURRING,
            EventType::TYPE_RETREAT => self::KIND_FIXED_DURATION_START_DATE,
            default => self::KIND_SINGLE_START_DATE,
        };
    }

    public static function fixedDurationDays(?string $groupType, ?string $eventTypeName = null): ?int
    {
        if ($groupType !== EventType::TYPE_RETREAT) {
            return null;
        }

        if ($eventTypeName === EventType::CAPTIVATING_CORE_RETREAT_1_DAY) {
            return 1;
        }

        return 4;
    }

    public static function requiresFrequency(?string $groupType): bool
    {
        return $groupType === EventType::TYPE_FIRE;
    }

    public static function retreatEndDateOffsetDays(?string $groupType, ?string $eventTypeName = null): ?int
    {
        $fixedDurationDays = self::fixedDurationDays($groupType, $eventTypeName);

        if ($fixedDurationDays === null) {
            return null;
        }

        return $fixedDurationDays - 1;
    }
}
