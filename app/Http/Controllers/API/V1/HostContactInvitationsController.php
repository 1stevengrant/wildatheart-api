<?php

namespace App\Http\Controllers\API\V1;

use App\Models\ContactInvitation;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Data\V1\StoreContactInvitationData;
use App\Data\V1\ContactInvitationResponseData;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\ContactInvitation\CancelContactInvitation;
use App\Actions\ContactInvitation\CreateContactInvitation;

class HostContactInvitationsController extends Controller
{
    public function store(StoreContactInvitationData $data): JsonResponse
    {
        $invitation = CreateContactInvitation::run($this->user(), $data);

        return response()->json(new ContactInvitationResponseData(
            status: 'invitation_pending',
            invitation_id: $invitation->id,
            expires_at: $invitation->expires_at->toIso8601String(),
        ), Response::HTTP_ACCEPTED);
    }

    public function destroy(ContactInvitation $invitation): JsonResponse
    {
        CancelContactInvitation::run($this->user(), $invitation);

        return response()->json(['status' => 'cancelled']);
    }
}
