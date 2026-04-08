<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\Event;
use App\Models\EventType;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class GetEventBreakdown implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Gets a breakdown of event counts grouped by event type, with optional date range and status filters. Also includes total attendee counts per type. Use this for summary reports.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Event::query()
            ->select('event_type_id', DB::raw('COUNT(*) as event_count'), DB::raw('SUM(attendee_count) as total_attendees'))
            ->groupBy('event_type_id');

        if ($request->has('start_date') || $request->has('end_date')) {
            $query->betweenDates(
                $request['start_date'] ?? null,
                $request['end_date'] ?? null
            );
        }

        if ($request->has('status')) {
            $status = $request['status'];

            if ($status === 'published') {
                $query->published();
            } elseif ($status === 'unpublished') {
                $query->unpublished();
            }
        }

        if ($request->has('sex')) {
            $query->filterBySex($request['sex']);
        }

        if ($request->has('country')) {
            $query->inCountry([$request['country']]);
        }

        $results = $query->get();

        $eventTypes = EventType::query()->pluck('name', 'id');

        $breakdown = $results->map(fn ($row) => [
            'event_type' => $eventTypes[$row->event_type_id] ?? 'Unknown',
            'event_count' => $row->event_count,
            'total_attendees' => (int) $row->total_attendees,
        ])->sortByDesc('event_count')->values();

        $totals = [
            'total_events' => $breakdown->sum('event_count'),
            'total_attendees' => $breakdown->sum('total_attendees'),
        ];

        return json_encode([
            'breakdown' => $breakdown,
            'totals' => $totals,
            'filters_applied' => array_filter([
                'start_date' => $request['start_date'] ?? null,
                'end_date' => $request['end_date'] ?? null,
                'status' => $request['status'] ?? null,
                'sex' => $request['sex'] ?? null,
                'country' => $request['country'] ?? null,
            ]),
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()
                ->description('Start of date range (YYYY-MM-DD).'),
            'end_date' => $schema->string()
                ->description('End of date range (YYYY-MM-DD).'),
            'status' => $schema->string()
                ->description('Filter by status: "published" or "unpublished". Omit for all.'),
            'sex' => $schema->string()
                ->description('Filter by audience: "men" or "women". Omit for all.'),
            'country' => $schema->string()
                ->description('Filter by country name or code.'),
        ];
    }
}
