<?php

namespace App\Http\Controllers\Internal;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Queries\ScheduledFiresQuery;
use App\Enums\Event\ScheduleTestPreset;
use App\Data\Internal\FireScheduleTestRowData;

/**
 * Staging tool for walking a scheduled Fire through its reminder, renewal and lapse
 * rules without waiting real days.
 */
class FireSchedulesController extends Controller
{
    public function index(Request $request): View
    {
        $fires = ScheduledFiresQuery::paginate($request->string('search')->trim()->toString() ?: null);

        return view('internal.fire-schedules', [
            'fires' => $fires,
            'rows' => $fires->getCollection()->map(FireScheduleTestRowData::fromEvent(...)),
            'presets' => ScheduleTestPreset::cases(),
            'search' => $request->query('search'),
        ]);
    }
}
