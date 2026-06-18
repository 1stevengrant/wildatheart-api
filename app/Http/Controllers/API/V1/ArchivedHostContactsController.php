<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Carbon;
use App\Data\V1\HostContactData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Event\SetEventInterestArchivedState;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ArchivedHostContactsController extends Controller
{
    public function store(Event $event, User $user): JsonResponse
    {
        $interest = SetEventInterestArchivedState::run($this->user(), $event, $user, Carbon::now());

        if (! $interest) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json(HostContactData::fromInterest($interest));
    }

    public function destroy(Event $event, User $user): JsonResponse
    {
        $interest = SetEventInterestArchivedState::run($this->user(), $event, $user, null);

        if (! $interest) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json(HostContactData::fromInterest($interest));
    }
}
