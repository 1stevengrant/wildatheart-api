<?php

namespace App\Support;

use Carbon\Carbon;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventDateHelper
{
    public static function adjustEndDateForRetreat(Request $request, string $groupType, ?string $eventTypeName = null): void
    {
        $days = match (true) {
            $eventTypeName === EventType::SURVIVAL_SCHOOL && $groupType === EventType::TYPE_RETREAT => 4,
            $eventTypeName === EventType::WAH_RETREAT && $groupType === EventType::TYPE_RETREAT => 3,
            $groupType === EventType::TYPE_RETREAT => 3,  // Default for any retreat
            default => null
        };

        ray([
            'event_type_name' => $eventTypeName,
            'group_type' => $groupType,
            'days' => $days,
        ]);

        if (isset($days)) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays($days)->format('Y-m-d'),
            ]);
        }

    }
}
