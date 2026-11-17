<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

#[TypeScript]
class InternalEventSearchData extends Data
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $host_name,
        public ?string $host_email,
        public ?string $host_phone,
        public ?string $city,
        public ?string $state,
        public ?string $event_type,
        public ?string $frequency,
        public ?int $attendee_count,
        public ?string $start_date,
        public ?float $distance,
        public string $status,
        public ?string $host_street,
        public ?string $host_address_city,
        public ?string $host_address_state,
        public ?string $host_zip,
        public ?string $host_country,
        public ?string $host_wegive_id,
        public ?string $created_at,
        public string $privacy,
    ) {}

    public static function fromModel(Event $event, ?float $distance): self
    {
        return new self(
            id: $event->id,
            name: $event->name,
            host_name: $event->user?->name,
            host_email: $event->user?->email,
            host_phone: $event->user?->phone,
            city: $event->city,
            state: $event->state,
            event_type: $event->eventType?->name,
            frequency: $event->frequency?->value,
            attendee_count: $event->attendee_count,
            start_date: $event->start_date?->format('Y-m-d'),
            distance: $distance !== null ? round($distance, 2) : null,
            status: self::getEventStatus($event),
            host_street: $event->user?->primaryAddress?->street_address,
            host_address_city: $event->user?->primaryAddress?->city,
            host_address_state: $event->user?->primaryAddress?->state,
            host_zip: $event->user?->primaryAddress?->zip,
            host_country: $event->user?->primaryAddress?->country,
            host_wegive_id: $event->user?->wegive_donor_id,
            created_at: $event->created_at?->format('Y-m-d'),
            privacy: $event->is_private ? 'Private' : 'Public',
        );
    }

    /**
     * @param  LengthAwarePaginator<int, Event>  $paginator
     * @return array{
     *     data: array<int, array<string, mixed>>,
     *     links: array<string, ?string>,
     *     meta: array<string, mixed>
     * }
     */
    public static function paginatedResults(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => collect($paginator->items())
                ->map(fn (Event $event) => self::fromModel($event, $event->distance ?? null)->toArray())
                ->all(),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'links' => $paginator->linkCollection()->toArray(),
            ],
        ];
    }

    private static function getEventStatus(Event $event): string
    {
        if ($event->trashed()) {
            return 'Deleted';
        }

        if (! $event->approved_at) {
            return 'Unpublished';
        }

        return 'Published';
    }
}
