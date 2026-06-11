<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Data\V1\HostContactData;
use App\Models\EventUserInterest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\ArchivedInterestFilter;

class HostContactsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $host = $this->user();

        $eventIds = $host->events()->pluck('events.id')
            ->merge($host->managedEvents()->pluck('events.id'))
            ->unique()
            ->values();

        if ($eventIds->isEmpty()) {
            return response()->json(HostContactData::collect([]));
        }

        $base = EventUserInterest::query()
            ->select('event_user_interests.*')
            ->whereIn('event_user_interests.event_id', $eventIds)
            ->join('users', 'event_user_interests.user_id', '=', 'users.id')
            ->join('events', 'event_user_interests.event_id', '=', 'events.id')
            ->with(['user', 'event']);

        $contacts = QueryBuilder::for($base)
            ->allowedFilters(
                AllowedFilter::partial('event', 'events.name'),
                AllowedFilter::custom('archived', new ArchivedInterestFilter)->default('false'),
            )
            ->allowedSorts(
                AllowedSort::field('name', 'users.given_name'),
                AllowedSort::field('email', 'users.email'),
                AllowedSort::field('created_at', 'event_user_interests.created_at'),
            )
            ->defaultSort('-event_user_interests.created_at')
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();

        $contacts->getCollection()->transform(
            fn (EventUserInterest $interest): HostContactData => HostContactData::fromInterest($interest)
        );

        return response()->json($contacts);
    }
}
