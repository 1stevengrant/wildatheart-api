<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class EventDateHelper
{
    public static function adjustEndDateForRetreat(
        Request $request,
        ?string $groupType,
        ?string $eventTypeName = null,
    ): void {
        if (! $request->filled('start_date')) {
            return;
        }

        $days = EventTypeDateSelector::retreatEndDateOffsetDays($groupType, $eventTypeName);

        if ($days !== null) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays($days)->format('Y-m-d'),
            ]);
        }
    }

    public static function endDateForRetreat(
        ?string $startDate,
        ?string $endDate,
        ?string $groupType,
        ?string $eventTypeName = null,
    ): ?string {
        if (blank($startDate)) {
            return $endDate;
        }

        $days = EventTypeDateSelector::retreatEndDateOffsetDays($groupType, $eventTypeName);

        if ($days === null) {
            return $endDate;
        }

        return Carbon::parse($startDate)->addDays($days)->format('Y-m-d');
    }
}
