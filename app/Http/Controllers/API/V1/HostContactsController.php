<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Data\V1\HostContactData;
use App\Models\EventUserInterest;
use Illuminate\Http\JsonResponse;
use App\Jobs\HostContactsExportJob;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use App\Data\V1\HostContactsIndexData;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\ArchivedInterestFilter;
use App\Filters\HostContactSearchFilter;
use Illuminate\Database\Eloquent\Builder;

class HostContactsController extends Controller
{
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter' => ['nullable', 'array'],
            'filter.search' => ['nullable', 'string', 'max:100'],
            'filter.event' => ['nullable', 'string'],
            'filter.archived' => ['nullable', Rule::in(['false', 'true', 'all'])],
            'filter.added_from' => ['nullable', Rule::date()->format('Y-m-d')],
            'filter.added_to' => ['nullable', Rule::date()->format('Y-m-d')],
            'contacts' => ['nullable', 'array', 'max:1000'],
            'contacts.*.event_id' => ['required', 'uuid'],
            'contacts.*.user_id' => ['required', 'integer'],
        ]);

        HostContactsExportJob::dispatch(
            hostId: $this->user()->id,
            filters: $validated['filter'] ?? [],
            selectedContacts: $validated['contacts'] ?? [],
        );

        return response()->json([
            'message' => 'Your contacts export is being generated. You will receive an email shortly.',
        ]);
    }

    public function index(HostContactsIndexData $data): JsonResponse
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
            ->with(['user', 'event.eventType']);

        $contacts = QueryBuilder::for($base)
            ->allowedFilters(
                AllowedFilter::custom('search', new HostContactSearchFilter($eventIds->all())),
                AllowedFilter::exact('event', 'events.id'),
                AllowedFilter::custom('archived', new ArchivedInterestFilter)->default('false'),
                AllowedFilter::callback(
                    'added_from',
                    fn (Builder $query, mixed $value) => $query->whereDate(
                        'event_user_interests.created_at',
                        '>=',
                        $value,
                    ),
                ),
                AllowedFilter::callback(
                    'added_to',
                    fn (Builder $query, mixed $value) => $query->whereDate(
                        'event_user_interests.created_at',
                        '<=',
                        $value,
                    ),
                ),
            )
            ->allowedSorts(
                AllowedSort::field('name', 'users.given_name'),
                AllowedSort::field('email', 'users.email'),
                AllowedSort::field('created_at', 'event_user_interests.created_at'),
            )
            ->defaultSort('-event_user_interests.created_at')
            ->paginate($data->per_page ?? 25)
            ->withQueryString();

        $contacts->getCollection()->transform(
            fn (EventUserInterest $interest): HostContactData => HostContactData::fromInterest($interest)
        );

        return response()->json($contacts);
    }
}
