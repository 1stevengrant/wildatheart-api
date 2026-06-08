<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Carbon;
use App\Data\V1\HostContactData;
use App\Models\EventUserInterest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ArchivedHostContactsController extends Controller
{
    public function store(Event $event, User $user): JsonResponse
    {
        return $this->setArchivedAt($event, $user, Carbon::now());
    }

    public function destroy(Event $event, User $user): JsonResponse
    {
        return $this->setArchivedAt($event, $user, null);
    }

    private function setArchivedAt(Event $event, User $user, ?Carbon $archivedAt): JsonResponse
    {
        if (! $event->isOwnedOrManagedBy($this->host())) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $interestQuery = EventUserInterest::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id);

        if (! $interestQuery->exists()) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $interestQuery->update([
            'archived_at' => $archivedAt,
            'updated_at' => Carbon::now(),
        ]);

        $interest = EventUserInterest::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->with(['user', 'event'])
            ->first();

        return response()->json(HostContactData::fromInterest($interest));
    }

    private function host(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
