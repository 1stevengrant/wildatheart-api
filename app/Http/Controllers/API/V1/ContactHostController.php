<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Actions\User\UpdateUser;
use App\Data\V1\ContactHostData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Actions\User\FindOrCreateUser;
use App\Actions\Attendee\CreateAttendee;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

class ContactHostController extends Controller
{
    public function store(ContactHostData $data, Event $event): JsonResponse
    {
        $verifiedEmail = $this->validateContactToken($data->contact_token, $event);

        if (! $verifiedEmail) {
            return response()->json([
                'message' => 'Invalid or expired verification token. Please verify your email again.',
            ], 422);
        }

        if (mb_strtolower($data->email) !== $verifiedEmail) {
            return response()->json([
                'message' => 'Email does not match the verified email address.',
            ], 422);
        }

        $user = FindOrCreateUser::run($verifiedEmail, [
            'given_name' => $data->given_name,
            'family_name' => $data->family_name,
            'phone' => $data->phone,
        ]);

        UpdateUser::run($user, array_filter([
            'agreed_to_terms_at' => $user->hasAgreedToTerms() ? null : now(),
            'given_name' => $user->given_name ?: $data->given_name,
            'family_name' => $user->family_name ?: $data->family_name,
            'phone' => $user->phone ?: $data->phone,
        ]));

        $attendee = CreateAttendee::run($event, [
            'contact_user_id' => $user->id,
            'given_name' => $data->given_name,
            'family_name' => $data->family_name,
            'email' => $data->email,
            'phone' => $data->phone,
            'city' => $data->city,
            'state' => $data->state,
            'country' => $data->country,
            'marketing_updates' => $data->opted_into_marketing,
        ]);

        LogActivityJob::dispatch(
            description: 'Connected with Host',
            properties: ['attendee' => $attendee, 'event' => $event],
            causedBy: $event,
            performedOn: $attendee,
        );

        if ($attendee->hasOptedIntoMarketing()) {
            SubscribeToCommunicationListJob::dispatch(
                listMember: $attendee,
                listId: config('services.wegive.marketing_list_id')
            );
        }

        IncreaseEventCountsJob::dispatch($event, Event::COUNT_TYPE_ATTENDEE);

        PostAttendeeContactToSlackJob::dispatch($attendee, $event);

        return response()->json([
            'success' => true,
            'message' => "Connected with {$event->user->fullname}!",
        ]);
    }

    private function validateContactToken(string $token, Event $event): ?string
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Exception) {
            return null;
        }

        if (! isset($payload['email'], $payload['event_id'], $payload['expires_at'])) {
            return null;
        }

        if ($payload['event_id'] !== $event->id) {
            return null;
        }

        if (now()->isAfter($payload['expires_at'])) {
            return null;
        }

        return $payload['email'];
    }
}
