<?php

namespace App\Support;

use Carbon\Carbon;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventDateHelper
{
    public static function adjustEndDateForRetreat(Request $request, string $groupType): void
    {
        // For retreats with day_count (like Captivating Retreats), use the day_count from the request
        if ($groupType === EventType::TYPE_RETREAT && $request->has('day_count')) {
            $dayCount = (int) $request->input('day_count');
            $daysToAdd = max(0, $dayCount - 1);

            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays($daysToAdd)->format('Y-m-d'),
            ]);

            return;
        }

        // For regular retreats without day_count, use the default 3 days
        $days = match (true) {
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
