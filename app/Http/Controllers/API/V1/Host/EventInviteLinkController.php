<?php

namespace App\Http\Controllers\API\V1\Host;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Event\EnsureHostInviteToken;
use App\Http\Requests\API\V1\ShowHostEventInviteLinkRequest;

class EventInviteLinkController extends Controller
{
    public function show(ShowHostEventInviteLinkRequest $request, Event $event): JsonResponse
    {
        return response()->json([
            'url' => EnsureHostInviteToken::run($event),
        ]);
    }
}
