<?php

namespace App\Nova;

use App\Nova\Actions\ApproveEvent;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Event>
     */
    public static $model = \App\Models\Event::class;

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
                ->readonly()
                ->hideFromIndex(),

            Text::make('City')
                ->sortable()
                ->rules('required'),

            Text::make('State'),

            Text::make('Zip')
                ->sortable(),

            Text::make('Country')
                ->sortable(),

            Text::make('Latitude')
                ->hideFromIndex(),

            Text::make('Longitude')
                ->hideFromIndex(),

            Text::make('Region')->readonly(),

            Text::make('District')->readonly(),

            Text::make('Locality')->readonly(),

            Text::make('Neighborhood')->readonly(),

            DateTime::make('Approved At', 'approved_at')
                ->hideFromIndex()
                ->readonly(),

            DateTime::make('Created At'),

            DateTime::make('Deleted At')
                ->hideFromIndex(),

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
            new ApproveEvent(),
        ];
    }
}
