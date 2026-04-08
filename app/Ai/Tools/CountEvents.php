<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\Event;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class CountEvents implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Counts events matching the given filters. Can filter by event type ID, group type (fire/retreat/experience), date range, publication status, sex (men/women), and country.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Event::query();

        if ($request->has('event_type_id')) {
            $query->where('event_type_id', $request['event_type_id']);
        }

        if ($request->has('group_type')) {
            $query->filterByTypeGroup($request['group_type']);
        }

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

        $count = $query->count();

        return json_encode([
            'count' => $count,
            'filters_applied' => array_filter([
                'event_type_id' => $request['event_type_id'] ?? null,
                'group_type' => $request['group_type'] ?? null,
                'start_date' => $request['start_date'] ?? null,
                'end_date' => $request['end_date'] ?? null,
                'status' => $request['status'] ?? null,
                'sex' => $request['sex'] ?? null,
                'country' => $request['country'] ?? null,
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
                ->description('Filter by specific event type ID. Use GetEventTypes tool to find IDs.'),
            'group_type' => $schema->string()
                ->description('Filter by event group: "fire", "retreat", or "experience".'),
            'start_date' => $schema->string()
                ->description('Start of date range (YYYY-MM-DD). Filters on event start_date.'),
            'end_date' => $schema->string()
                ->description('End of date range (YYYY-MM-DD). Filters on event start_date.'),
            'status' => $schema->string()
                ->description('Filter by status: "published" or "unpublished".'),
            'sex' => $schema->string()
                ->description('Filter by audience: "men" or "women".'),
            'country' => $schema->string()
                ->description('Filter by country name or code.'),
        ];
    }
}
