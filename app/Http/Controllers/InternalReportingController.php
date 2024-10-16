<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class InternalReportingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        $date = $request->get('date') ?? now()->subDays(30)->startOfDay()->format('Y-m-d');

        return inertia('Internal/ReportingPage', [
            'date' => $date,
            'eventStats' => [
                'fires' => cache()->remember('fires.count_'.$date, now()->addMinutes(15), function () use ($date) {
                    return Event::query()->createdAfter($date)->fires()->count();
                }),
                'wahRetreats' => cache()->remember('wah-retreats.count_'.$date, now()->addMinutes(15), function () use ($date) {
                    return Event::query()->createdAfter($date)->wahRetreat()->count();
                }),
                'captivatingRetreats' => cache()->remember('captivating-retreats.count_'.$date, now()->addMinutes(15), function () use ($date) {
                    return Event::query()->createdAfter($date)->captivatingRetreat()->count();
                }),
                'experiences' => cache()->remember('experiences.count_'.$date, now()->addMinutes(15), function () use ($date) {
                    return Event::query()->createdAfter($date)->experiences()->count();
                }),
            ],
        ]);
    }
}
