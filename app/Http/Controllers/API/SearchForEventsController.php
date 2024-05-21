<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SearchForEventsRequest;
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

        $latitude = request('latitude');
        $longitude = request('longitude');
        $radius = request('radius', 10);
        $sex = request('sex');
        $typeGroup = request('type_group');
        $types = request('types');
        $startDate = request('start_date');
        $endDate = request('end_date');

        $events = Event::withinDistance(
            latitude: $latitude,
            longitude: $longitude,
            radius: $radius,
            sex: $sex,
            typeGroup: $typeGroup,
            types: $types,
            startDate: $startDate,
            endDate: $endDate
        )->get();

        return response()->json(['events' => $events]);
    }
}
