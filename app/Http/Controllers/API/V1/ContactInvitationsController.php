<?php

namespace App\Http\Controllers\API\V1;

use Throwable;
use App\Models\ContactInvitation;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\Auth0Profile;
use App\Data\V1\ContactInvitationEventData;
use App\Data\V1\ContactInvitationPreviewData;
use App\Data\V1\ResolveContactInvitationData;
use App\Services\Auth0\JwtAccessTokenVerifier;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\ContactInvitation\AcceptContactInvitation;
use App\Actions\ContactInvitation\DeclineContactInvitation;

class ContactInvitationsController extends Controller
{
    public function __construct(private readonly JwtAccessTokenVerifier $verifier) {}

    public function preview(ResolveContactInvitationData $data): JsonResponse
    {
        $invitation = $this->pendingInvitation($data->token);

        return response()->json(new ContactInvitationPreviewData(
            host_name: $invitation->invitedBy->fullname,
            events: $invitation->events
                ->map(fn ($event): ContactInvitationEventData => ContactInvitationEventData::fromModel($event))
                ->all(),
            expires_at: $invitation->expires_at->toIso8601String(),
        ));
    }

    public function accept(ResolveContactInvitationData $data): JsonResponse
    {
        $accessToken = request()->bearerToken();

        if (! $accessToken) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $claims = $this->verifier->verify($accessToken);
        } catch (Throwable) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $subject = $claims['sub'] ?? null;

        if (! is_string($subject) || $subject === '') {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(AcceptContactInvitation::run(
            $data->token,
            $subject,
            Auth0Profile::fromClaims($claims),
            $accessToken,
        ));
    }

    public function decline(ResolveContactInvitationData $data): JsonResponse
    {
        return response()->json(['status' => DeclineContactInvitation::run($data->token)]);
    }

    private function pendingInvitation(string $token): ContactInvitation
    {
        $invitation = ContactInvitation::query()
            ->with(['invitedBy', 'events.eventType'])
            ->where('token_hash', ContactInvitation::tokenHash($token))
            ->firstOrFail();

        if (! $invitation->isPending()) {
            abort(Response::HTTP_GONE, 'Invitation is no longer available.');
        }

        return $invitation;
    }
}
