<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\Attendee;
use App\Support\CountryNames;
use Laravel\Ai\Tools\Request;
use App\Builders\EventBuilder;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Support\EventTypeResolver;
use Illuminate\Database\Eloquent\Builder;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class QueryAttendees implements Tool
{
    use EncodesToolResults;

    public const int MAX_LIST = 50;

    /** @var array<string, string> */
    private const array GROUP_EXPRESSIONS = [
        'event' => 'events.id',
        'event_type' => 'events.event_type_id',
        'event_country' => 'events.country',
        'event_state' => 'events.state',
        'attendee_country' => 'attendees.country',
        'month' => "DATE_FORMAT(attendees.created_at, '%Y-%m')",
        'year' => 'YEAR(attendees.created_at)',
    ];

    public function description(): Stringable|string
    {
        return 'Count, list or group attendee signups. Filter by event, event type, event location and signup date. Lists include attendee contact details.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'mode' => $schema->string()->enum(['count', 'list', 'group_by'])->required(),
            'group_by' => $schema->string()->enum(array_keys(self::GROUP_EXPRESSIONS))->description('Required when mode is group_by. month/year use the signup date.'),
            'event_id' => $schema->string()->description('Only signups for this event UUID.'),
            'event_types' => $schema->array()->items($schema->string())->description('Event type ids, slugs or names.'),
            'event_country' => $schema->string()->description('Event country name or ISO code.'),
            'event_state' => $schema->string()->description('Event state name or code.'),
            'signed_up_from' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'signed_up_to' => $schema->string()->format('date')->description('Inclusive, YYYY-MM-DD.'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_LIST)->description('Max attendees for list mode. Defaults to 25.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:count,list,group_by'],
            'group_by' => ['required_if:mode,group_by', 'nullable', 'in:' . implode(',', array_keys(self::GROUP_EXPRESSIONS))],
            'event_types' => ['nullable', 'array'],
            'signed_up_from' => ['nullable', 'date'],
            'signed_up_to' => ['nullable', 'date'],
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

        $query = Attendee::query()
            ->join('events', 'events.id', '=', 'attendees.event_id')
            ->whereNull('events.deleted_at')
            ->when($request->string('event_id')->value(), fn (Builder $query, string $eventId) => $query->where('attendees.event_id', $eventId))
            ->when($eventTypeIds, fn (Builder $query) => $query->whereIn('events.event_type_id', $eventTypeIds))
            ->when($request->string('event_country')->value(), fn (Builder $query, string $country) => $query->whereIn('events.country', CountryNames::variants($country)))
            ->when($request->string('event_state')->value(), fn (Builder $query, string $state) => $query->whereHas('event', fn (EventBuilder $query) => $query->inState($state)))
            ->when($request->date('signed_up_from'), fn (Builder $query, $date) => $query->where('attendees.created_at', '>=', $date->startOfDay()))
            ->when($request->date('signed_up_to'), fn (Builder $query, $date) => $query->where('attendees.created_at', '<=', $date->endOfDay()));

        return match ($validated['mode']) {
            'count' => $this->encode(['count' => $query->count('attendees.id')]),
            'list' => $this->encode($this->list($query, $validated['limit'] ?? 25)),
            'group_by' => $this->encode($this->groupBy($query, $validated['group_by'])),
        };
    }

    /**
     * @return array{total: int, returned: int, attendees: array<int, array<string, mixed>>}
     */
    private function list(Builder $query, int $limit): array
    {
        $total = (clone $query)->count('attendees.id');

        $attendees = $query
            ->select([
                'attendees.id',
                'attendees.given_name',
                'attendees.family_name',
                'attendees.email',
                'attendees.phone',
                'attendees.city',
                'attendees.state',
                'attendees.country',
                'attendees.created_at',
                'events.id as event_id',
                'events.name as event_name',
            ])
            ->latest('attendees.created_at')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn (object $attendee) => [
                'id' => $attendee->id,
                'name' => mb_trim("{$attendee->given_name} {$attendee->family_name}"),
                'email' => $attendee->email,
                'phone' => $attendee->phone,
                'location' => collect([$attendee->city, $attendee->state, $attendee->country])->filter()->implode(', '),
                'signed_up_at' => $attendee->created_at,
                'event' => ['id' => $attendee->event_id, 'name' => $attendee->event_name],
            ])
            ->all();

        return [
            'total' => $total,
            'returned' => count($attendees),
            'attendees' => $attendees,
        ];
    }

    /**
     * @return array{total: int, groups: array<int, array{bucket: mixed, count: int}>}
     */
    private function groupBy(Builder $query, string $groupBy): array
    {
        $expression = self::GROUP_EXPRESSIONS[$groupBy];

        if ($groupBy === 'event') {
            $query->selectRaw('events.name as label');
        }

        $groups = $query
            ->selectRaw("{$expression} as bucket, count(attendees.id) as aggregate")
            ->groupBy($groupBy === 'event' ? ['bucket', 'label'] : ['bucket'])
            ->orderByDesc('aggregate')
            ->limit(200)
            ->toBase()
            ->get();

        $eventTypeNames = $groupBy === 'event_type' ? EventTypeResolver::names() : [];

        $groups = $groups
            ->map(fn (object $group) => [
                'bucket' => match ($groupBy) {
                    'event' => "{$group->label} ({$group->bucket})",
                    'event_type' => $eventTypeNames[$group->bucket] ?? $group->bucket,
                    default => $group->bucket,
                },
                'count' => (int) $group->aggregate,
            ])
            ->all();

        return [
            'total' => array_sum(array_column($groups, 'count')),
            'groups' => $groups,
        ];
    }
}
