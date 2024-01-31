<?php

namespace App\Nova\Repeater;

use AlexAzartsev\Heroicon\Heroicon;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EventTypeDetailItem extends Repeatable
{
    public function fields(NovaRequest $request): array
    {
        return [
            Heroicon::make('Icon')
                ->rules('required'),
            Text::make('Headline')
                ->rules('required', 'max:255'),
            Text::make('Description')
                ->rules('required', 'max:255'),
        ];
    }
}
