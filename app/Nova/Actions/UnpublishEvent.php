<?php

namespace App\Nova\Actions;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class UnpublishEvent extends Action
{
    use InteractsWithQueue, Queueable;

    public $confirmText = 'Are you sure you want to unpublish this event(s)?';

    public $name = 'Unpublish Event(s)';

    public $confirmButtonText = 'Unpublish';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        Event::whereKey($models->pluck('id'))
            ->update(['approved_at' => null]);

        return Action::message('Event(s) unpublished!');
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
