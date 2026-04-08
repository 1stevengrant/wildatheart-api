<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\Attendee;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class CountAttendees implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Counts attendees matching the given filters. Can filter by event type ID, group type (fire/retreat/experience), date range (based on when the attendee registered), event date range, and sex.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Attendee::query();

        if ($request->has('event_type_id')) {
            $query->whereHas('event', fn ($q) => $q->where('event_type_id', $request['event_type_id']));
        }

        if ($request->has('group_type')) {
            $query->whereHas('event', fn ($q) => $q->filterByTypeGroup($request['group_type']));
        }

        if ($request->has('event_start_date') || $request->has('event_end_date')) {
            $query->whereHas('event', fn ($q) => $q->betweenDates(
                $request['event_start_date'] ?? null,
                $request['event_end_date'] ?? null
            ));
        }

        if ($request->has('registered_after')) {
            $query->where('created_at', '>=', $request['registered_after']);
        }

        if ($request->has('registered_before')) {
            $query->where('created_at', '<=', $request['registered_before']);
        }

        if ($request->has('sex')) {
            $query->whereHas('event', fn ($q) => $q->filterBySex($request['sex']));
        }

        if ($request->has('event_id')) {
            $query->where('event_id', $request['event_id']);
        }

        $count = $query->count();

        return json_encode([
            'count' => $count,
            'filters_applied' => array_filter([
                'event_type_id' => $request['event_type_id'] ?? null,
                'group_type' => $request['group_type'] ?? null,
                'event_start_date' => $request['event_start_date'] ?? null,
                'event_end_date' => $request['event_end_date'] ?? null,
                'registered_after' => $request['registered_after'] ?? null,
                'registered_before' => $request['registered_before'] ?? null,
                'sex' => $request['sex'] ?? null,
                'event_id' => $request['event_id'] ?? null,
            ]),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'event_type_id' => $schema->integer()
                ->description('Filter attendees by the event type ID of the event they attended.'),
            'group_type' => $schema->string()
                ->description('Filter by event group: "fire", "retreat", or "experience".'),
            'event_start_date' => $schema->string()
                ->description('Only count attendees for events starting on or after this date (YYYY-MM-DD).'),
            'event_end_date' => $schema->string()
                ->description('Only count attendees for events starting on or before this date (YYYY-MM-DD).'),
            'registered_after' => $schema->string()
                ->description('Only count attendees who registered on or after this date (YYYY-MM-DD).'),
            'registered_before' => $schema->string()
                ->description('Only count attendees who registered on or before this date (YYYY-MM-DD).'),
            'sex' => $schema->string()
                ->description('Filter by event audience: "men" or "women".'),
            'event_id' => $schema->string()
                ->description('Filter by a specific event UUID.'),
        ];
    }
}
