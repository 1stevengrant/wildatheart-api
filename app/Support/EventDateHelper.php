<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class EventDateHelper
{
    public static function adjustEndDateForRetreat(Request $request, string $eventTypeName): void
    {
        if (str_contains(strtolower($eventTypeName), 'retreat')) {
            $request->merge([
                'end_date' => Carbon::parse($request->start_date)->addDays(3)->format('Y-m-d'),
            ]);
        }
    }
}
