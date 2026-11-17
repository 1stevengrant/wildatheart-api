<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Builders\EventBuilder;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Data\InternalEventSearchCriteriaData;

class BuildInternalEventSearchQuery
{
    use AsAction;

    public function handle(InternalEventSearchCriteriaData $criteria): EventBuilder
    {
        $query = Event::query()->with(['user.primaryAddress', 'eventType']);

        if ($criteria->hasCoordinates()) {
            $query->withinRadius($criteria->lat, $criteria->lng, $criteria->radius);
        }

        if ($criteria->activeOnly) {
            $query->published();
        }

        $criteria->privacy->applyTo($query);

        if ($criteria->includeDeleted) {
            $query->withTrashed();
        }

        if ($criteria->eventTypeIds !== []) {
            $query->whereIn('event_type_id', $criteria->eventTypeIds);
        }

        if ($criteria->createdFrom !== null) {
            $query->where('created_at', '>=', $criteria->createdFrom);
        }

        if ($criteria->createdTo !== null) {
            $query->where('created_at', '<=', "{$criteria->createdTo} 23:59:59");
        }

        return $query;
    }
}
