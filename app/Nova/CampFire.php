<?php

namespace App\Nova;

use App\Nova\Actions\ApproveCampFire;
use App\Nova\Filters\ApprovalStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;

class CampFire extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CampFire>
     */
    public static $model = \App\Models\CampFire::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Host', 'user', User::class)
                ->rules('required'),

            Trix::make('Description')
                ->rules('required'),

            Text::make('Phone')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('Address')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('City')
                ->rules('required'),

            Text::make('State')
                ->rules('required'),

            Text::make('Zip')
                ->rules('required'),

            Text::make('Latitude')
                ->hideFromIndex(),

            Text::make('Longitude')
                ->hideFromIndex(),

            Date::make('Date')
                ->rules('required')
                ->sortable(),

            Text::make('Time')
                ->rules('required')
                ->hideFromIndex(),

            Number::make('Attendee Limit')
                ->rules('required', 'numeric', 'min:1'),

            Text::make('Attendee Count', 'attendee_count')
                ->hideFromIndex()
                ->readonly(),

            Text::make('Spaces Remaining', 'remaining')
                ->readonly(),

            Boolean::make('Approval Status', 'approved_at')
                ->exceptOnForms(),

            DateTime::make('Approved At', 'approved_at')
                ->hideFromIndex()
                ->readonly(),

            HasMany::make('Attendees'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            //            new ApprovalStatus()
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new ApproveCampFire(),
        ];
    }
}
