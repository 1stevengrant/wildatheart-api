<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\User;
use App\Builders\UserBuilder;
use Laravel\Ai\Tools\Request;
use App\Builders\EventBuilder;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Support\EventTypeResolver;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class QueryHosts implements Tool
{
    use EncodesToolResults;

    public const int MAX_LIST = 50;

    public function description(): Stringable|string
    {
        return 'Count or list hosts (users who own events) with contact details and event counts. Filter by event type, event location, upcoming events, inactivity and login history.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'mode' => $schema->string()->enum(['count', 'list'])->required(),
            'event_types' => $schema->array()->items($schema->string())->description('Only hosts with events of these types (ids, slugs or names).'),
            'event_country' => $schema->string()->description('Only hosts with events in this country (name or ISO code).'),
            'event_state' => $schema->string()->description('Only hosts with events in this state name or code.'),
            'published_events_only' => $schema->boolean()->description('Only consider published events. Defaults to false.'),
            'has_upcoming_events' => $schema->boolean()->description('true: hosts with at least one event starting today or later. false: hosts with no upcoming events.'),
            'inactive_for_days' => $schema->integer()->min(1)->description('Hosts whose events have all gone this many days without being updated.'),
            'never_logged_in' => $schema->boolean()->description('true: only hosts who have never logged in.'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_LIST)->description('Max hosts for list mode. Defaults to 25.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:count,list'],
            'event_types' => ['nullable', 'array'],
            'inactive_for_days' => ['nullable', 'integer', 'min:1'],
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

        $eventFilter = function (EventBuilder $query) use ($request, $eventTypeIds): void {
            $query
                ->when($eventTypeIds, fn (EventBuilder $query) => $query->whereIn('event_type_id', $eventTypeIds))
                ->when($request->string('event_country')->value(), fn (EventBuilder $query, string $country) => $query->matchingCountry($country))
                ->when($request->string('event_state')->value(), fn (EventBuilder $query, string $state) => $query->inState($state))
                ->when($request->boolean('published_events_only'), fn (EventBuilder $query) => $query->published());
        };

        $query = User::query()->whereHas('events', $eventFilter);

        $query
            ->when($request->has('has_upcoming_events') && $request->boolean('has_upcoming_events'), fn (UserBuilder $query) => $query->whereHas('events', fn (EventBuilder $query) => $query->upcoming()->tap($eventFilter)))
            ->when($request->has('has_upcoming_events') && ! $request->boolean('has_upcoming_events'), fn (UserBuilder $query) => $query->whereDoesntHave('events', fn (EventBuilder $query) => $query->upcoming()->tap($eventFilter)))
            ->when($request->integer('inactive_for_days'), fn (UserBuilder $query, int $days) => $query->whereDoesntHave('events', fn (EventBuilder $query) => $query->where('updated_at', '>=', now()->subDays($days))))
            ->when($request->boolean('never_logged_in'), fn (UserBuilder $query) => $query->hasNeverLoggedIn());

        if ($validated['mode'] === 'count') {
            return $this->encode(['count' => $query->count()]);
        }

        $total = (clone $query)->count();

        $hosts = $query
            ->withCount([
                'events',
                'events as upcoming_events_count' => fn (EventBuilder $query) => $query->upcoming(),
                'events as published_events_count' => fn (EventBuilder $query) => $query->published(),
            ])
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->limit($validated['limit'] ?? 25)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'state' => $user->state,
                'events_count' => $user->events_count,
                'published_events_count' => $user->published_events_count,
                'upcoming_events_count' => $user->upcoming_events_count,
                'last_login_at' => $user->last_login_at?->toDateString(),
            ])
            ->all();

        return $this->encode([
            'total' => $total,
            'returned' => count($hosts),
            'hosts' => $hosts,
        ]);
    }
}
