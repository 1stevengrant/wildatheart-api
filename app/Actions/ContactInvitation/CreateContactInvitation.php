<?php

namespace App\Actions\ContactInvitation;

use Throwable;
use App\Models\User;
use App\Models\Event;
use App\Models\ContactInvitation;
use Illuminate\Support\Facades\DB;
use App\Mail\ContactInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Data\V1\StoreContactInvitationData;
use Illuminate\Auth\Access\AuthorizationException;

class CreateContactInvitation
{
    use AsAction;

    public function handle(User $host, StoreContactInvitationData $data): ContactInvitation
    {
        $events = Event::query()->controllableByUser($host)->whereIn('id', $data->event_ids)->get();

        if ($events->count() !== count($data->event_ids)) {
            throw new AuthorizationException;
        }

        $email = mb_strtolower(str_replace(' ', '', $data->email));
        $token = mb_rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $activeKey = hash('sha256', "{$host->id}|{$email}");
        $stagedImagePath = $data->image?->store('contact-invitations', [
            'disk' => 's3',
            'visibility' => 'private',
        ]);
        $replacedImagePath = null;

        try {
            $invitation = DB::transaction(function () use ($host, $data, $email, $token, $activeKey, $stagedImagePath, $events, &$replacedImagePath): ContactInvitation {
                $existing = ContactInvitation::query()->where('active_key', $activeKey)->lockForUpdate()->first();

                if ($existing) {
                    $replacedImagePath = $existing->staged_image_path;
                    $existing->update(['active_key' => null, 'cancelled_at' => now()]);
                }

                $invitation = ContactInvitation::query()->create([
                    'invited_by_user_id' => $host->id,
                    'existing_user_id' => User::query()->where('email', $email)->value('id'),
                    'email' => $email,
                    'given_name' => $data->given_name,
                    'family_name' => $data->family_name,
                    'phone' => $data->phone,
                    'staged_image_path' => $stagedImagePath,
                    'token_hash' => ContactInvitation::tokenHash($token),
                    'active_key' => $activeKey,
                    'expires_at' => now()->addDays(7),
                ]);
                $invitation->events()->attach($events->modelKeys());

                return $invitation;
            });
        } catch (Throwable $exception) {
            if ($stagedImagePath) {
                Storage::disk('s3')->delete($stagedImagePath);
            }

            throw $exception;
        }

        if ($replacedImagePath) {
            Storage::disk('s3')->delete($replacedImagePath);
        }

        Mail::to($email)->queue((new ContactInvitationMail(
            invitation: $invitation,
            token: $token,
            accountExisted: $invitation->existing_user_id !== null,
        )));

        return $invitation;
    }
}
