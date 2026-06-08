<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Data\V1\EventInterestData;
use App\Http\Controllers\Controller;
use App\Actions\Event\DispatchEventInterestNotifications;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EventInterestController extends Controller
{
    public function show(string $event): JsonResponse|Response
    {
        $createdAt = $this->createdAtFor($this->user(), $event);

        if (! $createdAt) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json($this->payload($this->user(), $event, $createdAt));
    }

    public function store(string $event): JsonResponse
    {
        $user = $this->user();
        $createdAt = $this->createdAtFor($user, $event);

        if (! $createdAt) {
            $eventModel = Event::with(['user', 'managers', 'eventType'])->findOrFail($event);

            $now = Carbon::now();
            $user->interestedEvents()->newPivotStatement()->insert([
                'user_id' => $user->id,
                'event_id' => $event,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $createdAt = $now;

            DispatchEventInterestNotifications::run($eventModel, $user);
        }

        return response()->json($this->payload($user, $event, $createdAt));
    }

    public function destroy(string $event): Response
    {
        $user = $this->user();

        $user->interestedEvents()->newPivotStatement()
            ->where('user_id', $user->id)
            ->where('event_id', $event)
            ->delete();

        return response()->noContent();
    }

    private function createdAtFor(User $user, string $event): ?Carbon
    {
        $row = $user->interestedEvents()->newPivotStatement()
            ->where('user_id', $user->id)
            ->where('event_id', $event)
            ->value('created_at');

        return $row ? Carbon::parse($row) : null;
    }

    private function payload(User $user, string $event, Carbon $createdAt): EventInterestData
    {
        return EventInterestData::from([
            'event_id' => $event,
            'user_id' => $user->id,
            'created_at' => $createdAt->toIso8601String(),
        ]);
    }

    private function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
