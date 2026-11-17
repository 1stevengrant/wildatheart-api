<?php

namespace App\Enums\Internal;

use App\Builders\EventBuilder;

enum ReportingEventType: string
{
    case Fires = 'fires';
    case WahRetreats = 'wahRetreats';
    case SurvivalSchools = 'survivalSchools';
    case CaptivatingRetreats = 'captivatingRetreats';
    case CaptivatingFires = 'captivatingFires';
    case Experiences = 'experiences';

    public function scope(EventBuilder $query): EventBuilder
    {
        return match ($this) {
            self::Fires => $query->fires(),
            self::WahRetreats => $query->wahRetreat(),
            self::SurvivalSchools => $query->survivalSchool(),
            self::CaptivatingRetreats => $query->captivatingRetreat(),
            self::CaptivatingFires => $query->captivatingFire(),
            self::Experiences => $query->experiences(),
        };
    }

    /**
     * Fires have no meaningful start date for a year, so a fire counts towards
     * the year it was last updated in (stopped or kept burning).
     */
    public function countsYearByUpdatedAt(): bool
    {
        return match ($this) {
            self::Fires, self::CaptivatingFires => true,
            default => false,
        };
    }
}
