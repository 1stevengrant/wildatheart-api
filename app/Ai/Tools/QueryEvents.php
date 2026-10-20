<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\Event;
use Laravel\Ai\Tools\Request;
use App\Builders\EventBuilder;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Support\EventTypeResolver;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class QueryEvents implements Tool
{
    use EncodesToolResults;

    public const int MAX_LIST = 50;

    /** @var array<string, string> */
    private const array GROUP_EXPRESSIONS = [
        'country' => 'country',
        'state' => 'state',
        'city' => 'city',
        'event_type' => 'event_type_id',
        'month' => "DATE_FORMAT(start_date, '%Y-%m')",
        'year' => 'YEAR(start_date)',
        'created_month' => "DATE_FORMAT(created_at, '%Y-%m')",
        'created_year' => 'YEAR(created_at)',
    ];

    public function description(): Stringable|string
    {
        return 'Count, list or group events (fires, retreats, experiences). Supports filters for event type, location, status, dates and host. Prefer this over SQL for event questions.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'mode' => $schema->string()->enum(['count', 'list', 'group_by'])->description('count returns a number, list returns up to 50 events, group_by returns counts per bucket.')->required(),
            'group_by' => $schema->string()->enum(array_keys(self::GROUP_EXPRESSIONS))->description('Required when mode is group_by. month/year use start_date, created_month/created_year use created_at.'),
            'event_types' => $schema->array()->items($schema->string())->description('Event type ids, slugs or names, e.g. ["fires", "captivating-fires"].'),
            'country' => $schema->string()->description('Country name or ISO code, e.g. "United States" or "US". Matches both stored forms.'),
            'state' => $schema->string()->description('State or province name (partial match) or exact state code, e.g. "Texas" or "TX".'),
            'city' => $schema->string()->description('City name, partial match.'),
            'statuses' => $schema->array()->items($schema->string()->enum(['published', 'unpublished', 'upcoming', 'active', 'inactive', 'public', 'private']))->description('All given statuses must apply.'),
            'start_date_from' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'start_date_to' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'created_from' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'created_to' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'host_id' => $schema->integer()->description('Only events owned by this user id.'),
            'include_deleted' => $schema->boolean()->description('Include soft deleted events. Defaults to false.'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_LIST)->description('Max events for list mode. Defaults to 25.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:count,list,group_by'],
            'group_by' => ['required_if:mode,group_by', 'nullable', 'in:' . implode(',', array_keys(self::GROUP_EXPRESSIONS))],
            'event_types' => ['nullable', 'array'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['in:published,unpublished,upcoming,active,inactive,public,private'],
            'start_date_from' => ['nullable', 'date'],
            'start_date_to' => ['nullable', 'date'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'host_id' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIST],
        ]);

        $eventTypeIds = null;

        if (! empty($validated['event_types'])) {
            $eventTypeIds = EventTypeResolver::ids($validated['event_types']);

            if ($eventTypeIds === []) {
                return $this->encode([
                    'error' => 'None of the given event types exist.',
                    'event_types' => EventTypeResolver::names(),
                ]);
            }
        }

        $query = $this->buildQuery($request, $eventTypeIds);

        return match ($validated['mode']) {
            'count' => $this->encode(['count' => $query->count()]),
            'list' => $this->encode($this->list($query, $validated['limit'] ?? 25)),
            'group_by' => $this->encode($this->groupBy($query, $validated['group_by'])),
        };
    }

    /**
     * @param  array<int, int>|null  $eventTypeIds
     */
    private function buildQuery(Request $request, ?array $eventTypeIds): EventBuilder
    {
        $query = Event::query()->without('eventType');

        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        $query
            ->when($eventTypeIds, fn (EventBuilder $query) => $query->whereIn('event_type_id', $eventTypeIds))
            ->when($request->string('country')->value(), fn (EventBuilder $query, string $country) => $query->matchingCountry($country))
            ->when($request->string('state')->value(), fn (EventBuilder $query, string $state) => $query->inState($state))
            ->when($request->string('city')->value(), fn (EventBuilder $query, string $city) => $query->inCity($city))
            ->when($request->integer('host_id'), fn (EventBuilder $query, int $hostId) => $query->belongsToUser($hostId))
            ->when($request->date('start_date_from'), fn (EventBuilder $query, $date) => $query->where('start_date', '>=', $date->startOfDay()))
            ->when($request->date('start_date_to'), fn (EventBuilder $query, $date) => $query->where('start_date', '<=', $date->endOfDay()))
            ->when($request->date('created_from'), fn (EventBuilder $query, $date) => $query->where('created_at', '>=', $date->startOfDay()))
            ->when($request->date('created_to'), fn (EventBuilder $query, $date) => $query->where('created_at', '<=', $date->endOfDay()));

        foreach ($request->array('statuses') as $status) {
            $query->{$status}();
        }

        return $query;
    }

    /**
     * @return array{total: int, returned: int, events: array<int, array<string, mixed>>}
     */
    private function list(EventBuilder $query, int $limit): array
    {
        $total = (clone $query)->count();

        $events = $query
            ->with(['eventType:id,name', 'user:id,given_name,family_name,email,phone'])
            ->latest('start_date')
            ->limit($limit)
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'name' => $event->name,
                'event_type' => $event->eventType?->name,
                'host' => $event->user ? [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                    'email' => $event->user->email,
                    'phone' => $event->user->phone,
                ] : null,
                'city' => $event->getRawOriginal('city'),
                'state' => $event->state,
                'country' => $event->country,
                'start_date' => $event->start_date?->toDateString(),
                'published' => $event->approved_at !== null,
                'is_private' => (bool) $event->is_private,
                'attendee_count' => $event->attendee_count,
                'deleted' => $event->deleted_at !== null,
            ])
            ->all();

        return [
            'total' => $total,
            'returned' => count($events),
            'events' => $events,
        ];
    }

    /**
     * @return array{total: int, groups: array<int, array{bucket: mixed, count: int}>}
     */
    private function groupBy(EventBuilder $query, string $groupBy): array
    {
        $expression = self::GROUP_EXPRESSIONS[$groupBy];

        $groups = $query
            ->selectRaw("{$expression} as bucket, count(*) as aggregate")
            ->groupBy('bucket')
            ->orderByDesc('aggregate')
            ->limit(200)
            ->toBase()
            ->get();

        $eventTypeNames = $groupBy === 'event_type' ? EventTypeResolver::names() : [];

        $groups = $groups
            ->map(fn (object $group) => [
                'bucket' => $groupBy === 'event_type' ? ($eventTypeNames[$group->bucket] ?? $group->bucket) : $group->bucket,
                'count' => (int) $group->aggregate,
            ])
            ->all();

        return [
            'total' => array_sum(array_column($groups, 'count')),
            'groups' => $groups,
        ];
    }
}
