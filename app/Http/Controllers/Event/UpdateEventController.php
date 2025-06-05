<?php

namespace App\Http\Controllers\Event;

use Log;
use Gate;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Support\EventDateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Container\BindingResolutionException;

class UpdateEventController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        abort_unless(Gate::allows('update', $event), 403);

        try {

            $formRequest = $this->resolveFormRequest($event->eventType->group_type);

            if ($event->eventType->group_type === EventType::TYPE_RETREAT) {

                EventDateHelper::adjustEndDateForRetreat(
                    request: $request,
                    groupType: $event->eventType->group_type,
                );
            }

            $event->update($formRequest->validated());

            return to_route('my.events.active')->with([
                'notifications' => [
                    'message' => 'Event updated successfully!',
                    'type' => 'success',
                ],
            ]);
        } catch (BindingResolutionException $e) {

            Log::error('Failed to resolve form request: ' . $e->getMessage());

            return to_route('my.events.active')->with([
                'notifications' => [
                    'message' => 'There was an error processing your request.',
                    'type' => 'error',
                ],
            ]);
        }
    }

    /**
     * Resolve the appropriate FormRequest based on event type group
     *
     * @throws BindingResolutionException
     */
    protected function resolveFormRequest(string $eventTypeGroupType): FormRequest
    {
        $formRequestClass = match ($eventTypeGroupType) {
            EventType::TYPE_FIRE => FireEventRequest::class,
            default => UpdateEventRequest::class,
        };

        return app()->make($formRequestClass);
    }
}
