<?php

namespace App\Nova;

use App\Nova\Repeater\EventTypeDetailItem;
use App\Nova\Repeater\FaqItem;
use App\Nova\Repeater\TextBlock;
use App\Nova\Repeater\VideoBlock;
use App\Support\PublicVaporImage;
use Eminiarts\Tabs\Traits\HasTabs;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EventType extends Resource
{
    use HasTabs;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\EventType>
     */
    public static $model = \App\Models\EventType::class;

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
        'is_active',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable()->hideFromIndex(),
            Boolean::make('Is Active'),
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Tags')
                ->sortable()
                ->rules('nullable', 'max:255')
                ->help('Comma separated list of tags'),

            Select::make('Sex')
                ->help('Who is this event type for?')
                ->options([
                    \App\Models\Event::FOR_MEN => ucfirst(\App\Models\Event::FOR_MEN),
                    \App\Models\Event::FOR_WOMEN => ucfirst(\App\Models\Event::FOR_WOMEN),
                ]),

            PublicVaporImage::make('Image')
                ->path('event_types')
                ->help('This image will be used on event cards')
                ->thumbnail(static fn ($value, $disk): ?string => $value
                    ? novaThumbnail($value)
                    : null)
                ->storeAs(static function ($request): string {
                    $img = $request->input('vaporFile')['image'];

                    return str_replace('tmp/', '', (string) $img['key']).'.'.$img['extension'];
                }),

            PublicVaporImage::make('Hero Image')
                ->hideFromIndex()
                ->path('hero_images')
                ->help('This image will be used on event detail pages')
                ->thumbnail(static fn ($value, $disk): ?string => $value
                    ? novaThumbnail($value)
                    : null)
                ->storeAs(static function ($request): string {
                    $img = $request->input('vaporFile')['hero_image'];

                    return str_replace('tmp/', '', (string) $img['key']).'.'.$img['extension'];
                }),

            Text::make('Hero Video')
                ->hideFromIndex()
                ->help('This video will be used on event detail pages')
                ->rules('nullable', 'url', 'max:255'),

            Repeater::make('Key Details')
                ->repeatables([
                    EventTypeDetailItem::make(),
                ]),

            Markdown::make('Description'),

            Markdown::make('Card Teaser Text')
                ->hideFromIndex()
                ->help('This text will be shown on the event type card when selecting an event type.'),

            Text::make('Registration Welcome Video')
                ->hideFromIndex()
                ->help('This video will be shown on the registration page. It should be an MP4 file url.')
                ->rules('nullable', 'url', 'max:255'),

            Markdown::make('Registration Welcome Text')
                ->rules('required'),

            Markdown::make('Registration Before Your Event Text')
                ->rules('required'),

            Repeater::make('Event Schedule')
                ->hideFromIndex()
                ->repeatables([
                    TextBlock::make(),
                    VideoBlock::make(),
                ]),

            Repeater::make('Event FAQs')
                ->hideFromIndex()
                ->repeatables([
                    FaqItem::make(),
                ]),

            HasMany::make('Events'),
        ];
    }

    /**
     * Get the cards available for the request.
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
