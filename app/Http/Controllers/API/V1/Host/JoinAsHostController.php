<?php

namespace App\Http\Controllers\API\V1\Host;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Data\JoinAsHostEventData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Event\JoinEventAsHost;

class JoinAsHostController extends Controller
{
    public function show(Request $request, string $token): JsonResponse
    {
        $event = $this->findEvent($token);

        return response()->json(JoinAsHostEventData::fromEvent(
            $event,
            $token,
            $event->isOwnedOrManagedBy($request->user()),
        ));
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $event = $this->findEvent($token);

        JoinEventAsHost::run($event, $request->user());

        return response()->json(['message' => "You are now a host for {$event->name}!", 'event_id' => $event->id]);
    }

    private function findEvent(string $token): Event
    {
        return Event::query()
            ->with(['user', 'eventType'])
            ->where('host_invite_token', $token)
            ->firstOrFail();
    }
}
