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

        $days = match (true) {
            $groupType === EventType::TYPE_RETREAT && $eventTypeName === EventType::CAPTIVATING_CORE_RETREAT_1_DAY => 0,
            $groupType === EventType::TYPE_RETREAT => 3,
            default => null
        };

        $days = EventTypeDateSelector::retreatEndDateOffsetDays($groupType, $eventTypeName);

        if ($days !== null) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays($days)->format('Y-m-d'),
            ]);
        }

    }
}
