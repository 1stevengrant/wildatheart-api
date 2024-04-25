<?php

namespace App\Nova\Repeater;

use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;

class TextBlock extends Repeatable
{
    public static $icon = 'document-text';

    public $confirmRemoval = true;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Markdown::make('Text')->rules('required'),
        ];
    }
}
