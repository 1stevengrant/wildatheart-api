<?php

namespace App\Nova;

use App\Enums\CampfireFrequency;
use App\Nova\Actions\ApproveCampFire;
use App\Nova\Filters\ApprovalStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;

class CampFire extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Campfire>
     */
    public static $model = \App\Models\Campfire::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static function label()
    {
        return 'Fires';
    }

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
            BelongsTo::make('Host', 'user', User::class)
                ->rules('required'),

            Text::make('Organization')
                ->rules('max:255'),

            Trix::make('Description'),

            Text::make('Phone')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('Street Address')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('City')
                ->rules('required'),

            Select::make('Frequency')
                ->rules('required')
                ->options(CampfireFrequency::array()),

            Text::make('State')
                ->rules('required'),

            Text::make('Zip')
                ->rules('required'),

            Text::make('Latitude')
                ->hideFromIndex(),

            Text::make('Longitude')
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

            Boolean::make('Is Private'),

            DateTime::make('Approved At', 'approved_at')
                ->hideFromIndex()
                ->readonly(),

            Text::make('Book Requests', 'book_request_count')
                ->sortable()
                ->readonly(),

            Text::make('Book Shipments Count', 'book_shipments_count')
                ->sortable()
                ->readonly(),

            HasMany::make('Book Shipments'),

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
