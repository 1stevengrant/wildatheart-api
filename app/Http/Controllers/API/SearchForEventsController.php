<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SearchForEventsRequest;
use App\Http\Resources\API\EventAPIResource;
use App\Models\Event;
use Illuminate\Http\Request;

class SearchForEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SearchForEventsRequest $request)
    {
        $request->validated();

        $swLatitude = request('sw_latitude');
        $swLongitude = request('sw_longitude');
        $neLatitude = request('ne_latitude');
        $neLongitude = request('ne_longitude');
        $sex = request('sex');
        $typeGroup = request('type_group');
        $types = request('types');
        $startDate = request('start_date');
        $endDate = request('end_date');

        $events = Event::query()
            ->approved()
            ->active()
//            ->withinBoundingBox($swLatitude, $swLongitude, $neLatitude, $neLongitude)
            ->betweenDates($startDate, $endDate)
            ->filterBySex($sex)
            ->filterByTypeGroup($typeGroup)
            ->filterByEventTypes($types)
            ->orderBy('start_date')
            ->get();

        return EventAPIResource::collection($events);
    }
}
