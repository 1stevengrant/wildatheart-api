<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ArchivedInterestFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $normalised = is_string($value) ? mb_strtolower($value) : $value;

        if ($normalised === 'all') {
            return;
        }

        $showArchived = filter_var($normalised, FILTER_VALIDATE_BOOLEAN);

        if ($showArchived) {
            $query->whereNotNull('event_user_interests.archived_at');

            return;
        }

        $query->whereNull('event_user_interests.archived_at');
    }
}
