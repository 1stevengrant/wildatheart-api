<?php

namespace App\Nova\Repeater;

use App\Support\PublicVaporImage;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImageBlock extends Repeatable
{
    public static $icon = 'camera';

    public $confirmRemoval = true;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            PublicVaporImage::make('Image')
                ->path('pages')
                ->thumbnail(static fn ($value, $disk): ?string => $value
                    ? novaThumbnail($value)
                    : null)
                ->storeAs(static function ($request): string {
                    $img = $request->input('vaporFile')['image'];

                    return str_replace('tmp/', '', (string) $img['key']).'.'.$img['extension'];
                }),
            Text::make('Caption')->rules('max:255'),
        ];
    }
}
