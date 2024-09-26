<?php

namespace App\Http\Controllers;

use App\Http\Resources\API\EventAPIResource;
use App\Http\Resources\EventTypeResource;
use App\Models\Event;
use App\Models\EventType;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Support\Facades\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $this->setSEOMetaData();


        return inertia('HomePage', [
            'filters' => Request::all(['sex', 'group_type', 'event_types']),
            'events' => $this->getEvents(),
            'eventTypes' => $this->getEventTypes(),
        ]);
    }

    private function setSEOMetaData()
    {
        $image = url('img/opengraph.png');
        $title = 'Home - Wild at Heart Fires';
        $url = url()->current();
        SEOMeta::setTitle('Home')
            ->setCanonical($url);

        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->addProperty('type', 'articles')
            ->addImage($image)
            ->addProperty('locale', 'en-US');

        TwitterCard::setTitle($title)
            ->addImage($image)
            ->setSite('@wildatheart');

        JsonLd::setTitle($title)
            ->addImage($image)
            ->setUrl($url);

        JsonLd::generate();
    }

    private function getEvents()
    {
        $events = Event::query()->approved()->active()
            ->when(request('sex'), function ($query, $sex) {
                return $query->where('sex', $sex);
            })
            ->when(request('group_type'), function ($query, $groupType) {
                return $query->where('type_group', $groupType);
            })
            ->when(request('event_types'), function ($query, $types) {
                return $query->whereIn('event_type_id', $types);
            })
            ->when(request('start_date'), function ($query, $startDate) {
                return $query->where('start_date', '>=', $startDate);
            })
            ->when(request('end_date'), function ($query, $endDate) {
                return $query->where('start_date', '<=', $endDate);
            })
            ->with(['eventType', 'host'])
            ->orderBy('start_date', 'asc')
            ->get();
        return EventAPIResource::collection($events);
    }

    private function getEventTypes()
    {
        return cache()->remember('active_event_types', now()->addMinutes(30), function () {
            return EventTypeResource::collection(EventType::query()->active()->get());
        });

    }
}
