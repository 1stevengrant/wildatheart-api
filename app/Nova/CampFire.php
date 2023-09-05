<?php

namespace App\Nova;

use App\Nova\Actions\ApproveCampFire;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CampFire extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Campfire>
     */
    public static $model = \App\Models\Campfire::class;

    public static $with = ['bookShipments', 'attendees', 'user'];

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

    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Host', 'user', User::class)
                ->rules('required'),

            Text::make('Name')
                ->sortable()
                ->rules('required'),

            Text::make('Phone')
                ->hideFromIndex(),

            Text::make('Street Address')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('City')
                ->sortable()
                ->rules('required'),

            Text::make('State'),

            Text::make('Zip')
                ->sortable()
                ->rules('required'),

            Text::make('Country')
                ->sortable(),

            Text::make('Latitude')
                ->hideFromIndex(),

            Text::make('Longitude')
                ->hideFromIndex(),

            Number::make('Attendee Limit')
                ->hideFromIndex()
                ->rules('numeric', 'min:1', 'nullable'),

            Text::make('Attendee Count', 'attendee_count')
                ->hideFromIndex()
                ->readonly(),

            Text::make('Spaces Remaining', 'remaining')
                ->hideFromIndex()
                ->readonly(),

            Boolean::make('Is Private')
                ->hideFromIndex(),

            DateTime::make('Approved At', 'approved_at')
                ->hideFromIndex()
                ->readonly(),

            Text::make('Book Requests', 'book_request_count')
                ->sortable()
                ->readonly(),

            Text::make('Book Shipments Count', 'book_shipments_count')
                ->sortable()
                ->readonly(),

            DateTime::make('Created At'),

            HasMany::make('Book Shipments'),

            HasMany::make('Attendees'),

        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new ApproveCampFire(),
        ];
    }
}
