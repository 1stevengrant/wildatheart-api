<?php

namespace App\Nova\Repeater;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class VideoBlock extends Repeatable
{
    public static $icon = 'video-camera';

    public $confirmRemoval = true;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Video URL')->rules('required', 'url'),
        ];
    }
}
