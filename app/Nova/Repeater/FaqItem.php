<?php

namespace App\Nova\Repeater;

use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class FaqItem extends Repeatable
{
    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Question')->rules('required'),
            Markdown::make('Answer')->rules('required'),
        ];
    }
}
