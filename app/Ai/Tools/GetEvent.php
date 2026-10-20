<?php

namespace App\Ai\Tools;

use Stringable;
use App\Models\User;
use App\Models\Event;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class GetEvent implements Tool
{
    use EncodesToolResults;

    public const int MAX_MATCHES = 5;

    public function description(): Stringable|string
    {
        return 'Get full details for an event by its UUID, or search events by name (up to 5 matches). Includes host, managers, address and attendee count.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('The event UUID.'),
            'name' => $schema->string()->description('Part of the event name, used when the id is unknown.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $request->validate([
            'id' => ['required_without:name', 'nullable', 'string'],
            'name' => ['required_without:id', 'nullable', 'string'],
        ]);

        $events = Event::query()
            ->withTrashed()
            ->with(['eventType:id,name', 'user:id,given_name,family_name,email,phone', 'managers:id,given_name,family_name,email', 'address'])
            ->withCount('attendees')
            ->when(
                $request->string('id')->value(),
                fn ($query, string $id) => $query->whereKey($id),
                fn ($query) => $query->where('name', 'like', '%' . $request->string('name') . '%'),
            )
            ->latest('start_date')
            ->limit(self::MAX_MATCHES)
            ->get();

        if ($events->isEmpty()) {
            return $this->encode(['error' => 'No matching events found.']);
        }

        return $this->encode([
            'events' => $events->map(fn (Event $event) => [
                'id' => $event->id,
                'name' => $event->name,
                'event_type' => $event->eventType?->name,
                'host' => $event->user?->only(['id', 'name', 'email', 'phone']),
                'managers' => $event->managers->map(fn (User $manager) => $manager->only(['id', 'name', 'email']))->all(),
                'phone' => $event->phone,
                'location' => [
                    'street_address' => $event->street_address,
                    'city' => $event->getRawOriginal('city'),
                    'state' => $event->state,
                    'zip' => $event->zip,
                    'country' => $event->country,
                ],
                'mailing_address' => $event->address?->only(['given_name', 'family_name', 'street_address', 'address2', 'city', 'state', 'zip', 'country', 'phone']),
                'start_date' => $event->start_date?->toDateTimeString(),
                'end_date' => $event->end_date?->toDateTimeString(),
                'frequency' => $event->frequency?->value,
                'sex' => $event->sex?->value,
                'published_at' => $event->approved_at?->toDateTimeString(),
                'is_private' => (bool) $event->is_private,
                'attendees' => $event->attendees_count,
                'view_count' => $event->view_count,
                'created_at' => $event->created_at?->toDateTimeString(),
                'deleted_at' => $event->deleted_at?->toDateTimeString(),
            ])->all(),
        ]);
    }
}
