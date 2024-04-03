<?php

namespace App\Nova;

use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EmailTemplate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\EmailTemplate>
     */
    public static $model = \App\Models\EmailTemplate::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required', 'max:255'),

            Text::make('From Name')
                ->hideFromIndex()
                ->rules('max:255'),

            Text::make('From Email')
                ->hideFromIndex()
                ->rules('email', 'nullable', 'max:255'),

            Text::make('Subject')
                ->rules('required', 'max:255'),

            Markdown::make('Body')
                ->rules('required'),

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
        return [];
    }
}
