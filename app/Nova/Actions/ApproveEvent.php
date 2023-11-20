<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ApproveEvent extends Action
{
    use InteractsWithQueue, Queueable;

    public $confirmText = 'Are you sure you want to approve this event?';

    public $name = 'Approve Event';

    public $confirmButtonText = 'Approve';

    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $model) {
            if (! $model->isApproved()) {
                \App\Jobs\ApproveEvent::dispatch(
                    event: $model,
                    user: auth()->user()
                );
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
