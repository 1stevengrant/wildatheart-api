<?php

namespace App\Nova\Actions;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Http\Requests\NovaRequest;

class PublishEvent extends Action
{
    use InteractsWithQueue, Queueable;

    public $confirmText = 'Are you sure you want to publish this event(s)?';

    public $name = 'Publish Event(s)';

    public $confirmButtonText = 'Publish';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        Event::whereKey($models->pluck('id'))
            ->update(['approved_at' => now()]);

        return Action::message('Event(s) published!');
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
