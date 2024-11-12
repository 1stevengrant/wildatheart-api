<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\EventTypesFilter;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class ActiveHosts extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withFilters(
            $query
                ->select('users.id', 'users.email', 'users.given_name', 'users.family_name')
                ->leftJoin('events', 'users.id', '=', 'events.user_id')
                ->whereNotNull('events.approved_at')
                ->whereNull('events.deleted_at')
                ->groupBy('users.id')
        );
    }

    /**
     * Get the fields available to the lens.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->sortable(),
            Text::make('Email', 'email'),
            Text::make('Given Name', 'given_name'),
            Text::make('Family Name', 'family_name'),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new EventTypesFilter,
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'active-hosts';
    }
}
