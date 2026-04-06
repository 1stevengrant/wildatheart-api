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
            $groupType === EventType::TYPE_RETREAT && $eventTypeName === EventType::CAPTIVATING_CORE_RETREAT_1_DAY => 0,
            $groupType === EventType::TYPE_RETREAT => 3,
            default => null
        };

        if (isset($days)) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays($days)->format('Y-m-d'),
            ]);
        }

    }
}
