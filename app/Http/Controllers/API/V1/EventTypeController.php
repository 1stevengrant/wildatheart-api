<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Data\V1\EventTypeData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class EventTypeController extends Controller
{
    /**
     * Serialising an event type resolves its Statamic landing page, so the
     * cache holds the finished payload rather than just the query result.
     */
    private const CACHE_KEY = 'eventTypesWithCounts.serialised.v2';

    private const CACHE_TTL_SECONDS = 300;

    public function __invoke(Request $request): JsonResponse
    {
        $eventTypes = cache()->remember(self::CACHE_KEY, now()->addHours(6), function () {
            return EventTypeData::collect($this->eventTypes())->toArray();
        });

        $fields = $this->requestedFields($request, $eventTypes);

        if ($fields !== null) {
            $eventTypes = array_map(
                fn (array $eventType) => array_intersect_key($eventType, array_flip($fields)),
                $eventTypes
            );
        }

        return response()
            ->json($eventTypes)
            ->setPublic()
            ->setMaxAge(self::CACHE_TTL_SECONDS)
            ->setSharedMaxAge(self::CACHE_TTL_SECONDS);
    }

    /**
     * @return Collection<int, EventType>
     */
    private function eventTypes(): Collection
    {
        return EventType::query()
            ->select('event_types.*')
            ->active()
            ->withCount([
                'events as listings_count' => function ($query) {
                    $query->whereNotNull('approved_at')
                        ->where('is_private', false)
                        ->where(function ($q) {
                            $q->where('start_date', '>=', now())
                                ->orWhereNull('start_date');
                        });
                },
            ])
            ->addSelect([
                'countries_count' => Event::select(DB::raw('COUNT(DISTINCT country)'))
                    ->whereColumn('event_type_id', 'event_types.id')
                    ->whereNotNull('country')
                    ->whereNotNull('approved_at')
                    ->where('is_private', false)
                    ->where(function ($q) {
                        $q->where('start_date', '>=', now()->toDateString())
                            ->orWhereNull('start_date');
                    }),
            ])
            ->orderBy('order')
            ->get();
    }

    /**
     * The subset of fields the caller asked for, or null when they want them all.
     *
     * @param  array<int, array<string, mixed>>  $eventTypes
     * @return array<int, string>|null
     */
    private function requestedFields(Request $request, array $eventTypes): ?array
    {
        $fields = $request->query('fields');

        if (! is_string($fields) || mb_trim($fields) === '') {
            return null;
        }

        $requested = array_values(array_filter(array_map(mb_trim(...), explode(',', $fields))));
        $available = array_keys($eventTypes[0] ?? []);
        $unknown = array_diff($requested, $available);

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'fields' => 'Unknown field(s): ' . implode(', ', $unknown) . '. Available: ' . implode(', ', $available) . '.',
            ]);
        }

        return $requested;
    }
}
