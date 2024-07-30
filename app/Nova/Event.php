<?php

namespace App\Nova;

use App\Nova\Actions\PublishEvent;
use App\Nova\Actions\UnpublishEvent;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
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

    public static $with = ['attendees', 'user', 'eventType'];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static function label(): string
    {
        return 'Events';
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
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

            Text::make('District')->readonly()->hideFromIndex(),

            Text::make('Locality')->readonly()->hideFromIndex(),

            Text::make('Neighborhood')->readonly()->hideFromIndex(),

            Boolean::make('Published', function () {
                return $this->isApproved();
            })->readonly(),

            DateTime::make('Approved At', 'approved_at')
                ->hideFromIndex()
                ->readonly(),

            DateTime::make('Created At')
                ->hideFromIndex(),

            DateTime::make('Deleted At')
                ->hideFromIndex(),

            HasMany::make('Attendees'),

            BelongsTo::make('Event Type'),

        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [
        ];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new PublishEvent,
            new UnpublishEvent,
        ];
    }
}
