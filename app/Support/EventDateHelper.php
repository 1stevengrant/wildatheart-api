<?php

namespace App\Support;

use Carbon\Carbon;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventDateHelper
{
    public static function adjustEndDateForRetreat(Request $request, string $groupType): void
    {
        if (str_contains(mb_strtolower($groupType), EventType::TYPE_RETREAT)) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays(3)->format('Y-m-d'),
            ]);
        }
    }
}
