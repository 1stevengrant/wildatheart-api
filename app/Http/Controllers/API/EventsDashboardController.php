<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventsDashboardController extends Controller
{
    /**
     * Get statistics for the events dashboard.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Validate date range parameters if provided
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get all event types
        $eventTypes = EventType::all();

        // 1. Total number of Active Events (any published event)
        $totalActiveEvents = Event::query()
            ->approved()
            ->active()
            ->count();

        // 2. Total number of events per type (with public/private split)
        $eventsByType = [];
        foreach ($eventTypes as $eventType) {
            $publicCount = Event::query()
                ->approved()
                ->active()
                ->notPrivate()
                ->where('event_type_id', $eventType->id)
                ->count();

            $privateCount = Event::query()
                ->approved()
                ->active()
                ->private()
                ->where('event_type_id', $eventType->id)
                ->count();

            $eventsByType[$eventType->name] = [
                'public' => $publicCount,
                'private' => $privateCount,
                'total' => $publicCount + $privateCount,
            ];
        }

        // 3. Total number of events that were active within a time frame
        $eventsInTimeFrame = Event::query()
            ->approved()
            ->betweenDates($startDate, $endDate)
            ->count();

        // 4. Total number of events that were active within a time frame per type
        $eventsInTimeFrameByType = [];
        foreach ($eventTypes as $eventType) {
            $count = Event::query()
                ->approved()
                ->betweenDates($startDate, $endDate)
                ->where('event_type_id', $eventType->id)
                ->count();

            $eventsInTimeFrameByType[$eventType->name] = $count;
        }

        // 5. Number of unpublished Events per type
        $unpublishedEventsByType = [];
        foreach ($eventTypes as $eventType) {
            $count = Event::query()
                ->unpublished()
                ->where('event_type_id', $eventType->id)
                ->count();

            $unpublishedEventsByType[$eventType->name] = $count;
        }

        // 6. Total Number of times a host is contacted per event type
        // This would require additional data that might not be available in the current models
        // For now, we'll return placeholder data
        $hostContactsByType = [];
        foreach ($eventTypes as $eventType) {
            $hostContactsByType[$eventType->name] = 0; // Placeholder
        }

        // 7. Number of new Events with date range filter
        $newEventsInTimeFrame = Event::query()
            ->when($startDate, function ($query) use ($startDate) {
                return $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->where('created_at', '<=', $endDate);
            })
            ->count();

        // 8, 9, 10. Email click statistics
        // These would require additional data that might not be available in the current models
        // For now, we'll return placeholder data
        $emailStats = [
            'keep_burning_clicks' => 0,
            'extinguish_clicks' => 0,
            'no_action_emails' => 0,
        ];

        return response()->json([
            'total_active_events' => $totalActiveEvents,
            'events_by_type' => $eventsByType,
            'events_in_time_frame' => $eventsInTimeFrame,
            'events_in_time_frame_by_type' => $eventsInTimeFrameByType,
            'unpublished_events_by_type' => $unpublishedEventsByType,
            'host_contacts_by_type' => $hostContactsByType,
            'new_events_in_time_frame' => $newEventsInTimeFrame,
            'email_stats' => $emailStats,
        ]);
    }
}
