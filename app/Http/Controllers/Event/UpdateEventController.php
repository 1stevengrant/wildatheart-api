<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\EventType;
use App\Data\FireEventData;
use Illuminate\Http\Request;
use App\Data\UpdateEventData;
use App\Support\EventDateHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class UpdateEventController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        abort_unless(Gate::allows('update', $event), 403);

        $groupType = $event->eventType->group_type;

        if ($groupType === EventType::TYPE_RETREAT && $request->has('start_date')) {
            EventDateHelper::adjustEndDateForRetreat(
                request: $request,
                groupType: $groupType,
                eventTypeName: $event->eventType->name,
            );
        }

        $data = match ($groupType) {
            EventType::TYPE_FIRE => FireEventData::validateAndCreate($request),
            default => UpdateEventData::validateAndCreate($request),
        };

        $event->update($data->toArray());

        return to_route('my.events.active')->with([
            'notifications' => [
                'message' => 'Event updated successfully!',
                'type' => 'success',
            ],
        ]);
    }
}
