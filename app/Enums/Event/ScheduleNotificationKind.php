<?php

namespace App\Enums\Event;

enum ScheduleNotificationKind: string
{
    case DatesAdded = 'dates_added';
    case AddMoreDates = 'add_more_dates';
    case LastDate = 'last_date';
    case Unpublished = 'unpublished';
}
