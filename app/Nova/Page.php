<?php

namespace App\Nova;

use App\Nova\Repeater\ImageBlock;
use App\Nova\Repeater\TextBlock;
use App\Nova\Repeater\VideoBlock;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Page extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Page>
     */
    public static $model = \App\Models\Page::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
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

            Text::make('Title')->sortable()->rules('required', 'max:255'),

            Boolean::make('Is Active')->sortable()->default(true),

            BelongsTo::make('Created By', 'createdBy', User::class)->onlyOnIndex(),

            BelongsTo::make('Last Updated By', 'lastUpdatedBy', User::class)->onlyOnIndex(),

            Text::make('Slug')->sortable()->rules('required', 'max:255')->readonly(),

            Repeater::make('Content')
                ->hideFromIndex()
                ->repeatables([
                    TextBlock::make(),
                    VideoBlock::make(),
                    ImageBlock::make(),
                ]),

            Markdown::make('Notes')->hideFromIndex(),
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
        return [];
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
        return [];
    }
}
