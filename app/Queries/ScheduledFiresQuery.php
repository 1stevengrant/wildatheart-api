<?php

namespace App\Queries;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduledFiresQuery
{
    public static function paginate(?string $search): LengthAwarePaginator
    {
        return Event::query()
            ->allFires()
            ->withSchedule()
            ->with(['schedule', 'scheduleDates', 'scheduleNotifications', 'user', 'managers', 'eventType'])
            ->when($search, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $user) => $user->where('email', 'like', "%{$search}%"));
            }))
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();
    }
}
