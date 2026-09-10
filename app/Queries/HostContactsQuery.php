<?php

namespace App\Queries;

use App\Models\User;
use App\Models\EventUserInterest;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use App\Data\V1\HostContactsIndexData;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\ArchivedInterestFilter;
use App\Filters\HostContactSearchFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class HostContactsQuery
{
    public function paginate(User $host, HostContactsIndexData $data): LengthAwarePaginator
    {
        $eventIds = $this->eventIds($host);
        $base = EventUserInterest::query()
            ->select('event_user_interests.*')
            ->whereIn('event_user_interests.event_id', $eventIds)
            ->join('users', 'event_user_interests.user_id', '=', 'users.id')
            ->join('events', 'event_user_interests.event_id', '=', 'events.id')
            ->with(['user', 'event.eventType']);

        return QueryBuilder::for($base)
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
    }

    /** @return Collection<int, string> */
    private function eventIds(User $host): Collection
    {
        return $host->events()->pluck('events.id')
            ->merge($host->managedEvents()->pluck('events.id'))
            ->unique()
            ->values();
    }
}
