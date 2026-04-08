<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\EventType;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class GetEventTypes implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Lists all available event types with their IDs, names, group types, and active status. Call this first to understand which event types exist before querying events.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = EventType::query()
            ->select(['id', 'name', 'group_type', 'is_active', 'sex'])
            ->orderBy('group_type')
            ->orderBy('name');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $types = $query->get()
            ->map(fn (EventType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'group' => $type->group_type,
                'active' => $type->is_active,
                'sex' => $type->sex,
            ]);

        return json_encode($types, JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'active_only' => $schema->boolean()
                ->description('If true, only return active event types. Defaults to false.'),
        ];
    }
}
