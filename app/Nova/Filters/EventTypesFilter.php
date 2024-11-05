<?php

namespace App\Nova\Filters;

use App\Models\EventType;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class EventTypesFilter extends BooleanFilter
{
    public $name = 'Event Types';

    /**
     * The filter's component.
     *
     * @var string
     */

    /**
     * Apply the filter to the given query.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {

        // Doing gymnatistcs here because I couldn't get the ids out of the value array
        $types = EventType::all()->pluck('name', 'id');

        $selectedTypes = collect($value)->filter(function ($item, $key) use ($types) {
            return $item && $types->has($key);
        })->map(function ($item, $key) use ($types) {
            return $types[$key];
        })->keys()->toArray();

        if (!empty($selectedTypes)) {
            return $query->whereIn('events.event_type_id', $selectedTypes);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function options(NovaRequest $request)
    {

        $eventTypes = EventType::all();
        return $eventTypes->pluck('id', 'name');
    }
}
