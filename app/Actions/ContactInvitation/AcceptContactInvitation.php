<?php

namespace App\Actions\ContactInvitation;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Data\V1\HostContactData;
use App\Models\ContactInvitation;
use App\Models\EventUserInterest;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use App\Actions\User\ProvisionAuth0User;
use App\DataTransferObjects\Auth0Profile;
use Intervention\Image\Drivers\Gd\Driver;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Data\V1\ContactInvitationResolutionData;

class AcceptContactInvitation
{
    use AsAction;

    public function __construct(private readonly ProvisionAuth0User $provisionAuth0User) {}

    public function handle(string $token, string $subject, Auth0Profile $profile, string $accessToken): ContactInvitationResolutionData
    {
        if (! $profile->email || ! $profile->emailVerified) {
            abort(403, 'A verified email is required.');
        }

        $profile = new Auth0Profile(
            email: mb_strtolower(str_replace(' ', '', $profile->email)),
            emailVerified: true,
            givenName: $profile->givenName,
            familyName: $profile->familyName,
        );

        return DB::transaction(function () use ($token, $subject, $profile, $accessToken): ContactInvitationResolutionData {
            $invitation = ContactInvitation::query()
                ->where('token_hash', ContactInvitation::tokenHash($token))
                ->lockForUpdate()
                ->firstOrFail();

            if ($profile->email !== $invitation->email) {
                abort(403, 'Authenticated email does not match this invitation.');
            }

            if ($invitation->accepted_at) {
                return $this->resolution($invitation, 'already_accepted');
            }

            if ($invitation->declined_at || $invitation->cancelled_at) {
                abort(410, 'Invitation is no longer available.');
            }

            if ($invitation->expires_at->isPast()) {
                abort(410, 'Invitation has expired.');
            }

            $events = $invitation->events()->get();
            $controllableEventCount = Event::query()
                ->controllableByUser($invitation->invitedBy)
                ->whereIn('id', $events->modelKeys())
                ->count();

            if ($events->isEmpty() || $controllableEventCount !== $events->count()) {
                abort(410, 'Invitation events are no longer available.');
            }

            $accountExistedAtAcceptance = User::withTrashed()->where('email', $invitation->email)->exists();
            $stagedImagePath = $invitation->staged_image_path;
            $user = $this->provisionAuth0User->handle($subject, $profile, $accessToken);

            if (! $accountExistedAtAcceptance) {
                $user->fill([
                    'given_name' => $user->given_name ?: $invitation->given_name,
                    'family_name' => $user->family_name ?: $invitation->family_name,
                    'phone' => $user->phone ?: $invitation->phone,
                ])->save();

                $this->promoteStagedImage($invitation, $user);
            }

            $user->interestedEvents()->syncWithoutDetaching($events->modelKeys());
            $invitation->update(['active_key' => null, 'accepted_at' => now(), 'staged_image_path' => null]);

            if ($accountExistedAtAcceptance && $stagedImagePath) {
                Storage::disk('s3')->delete($stagedImagePath);
            }

            return $this->resolution($invitation, 'accepted');
        });
    }

    private function promoteStagedImage(ContactInvitation $invitation, User $user): void
    {
        if (! $invitation->staged_image_path || $user->image) {
            return;
        }

        $extension = mb_strtolower(pathinfo($invitation->staged_image_path, PATHINFO_EXTENSION));
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $destination = 'users/' . Str::uuid() . ".{$extension}";
        $source = Storage::disk('s3')->get($invitation->staged_image_path);
        $sanitized = (new ImageManager(new Driver))->decode($source)->encodeUsingFileExtension($extension);
        Storage::disk('s3')->put($destination, (string) $sanitized, 'public');
        Storage::disk('s3')->delete($invitation->staged_image_path);
        $user->forceFill(['image' => $destination])->save();
    }

    private function resolution(ContactInvitation $invitation, string $status): ContactInvitationResolutionData
    {
        $user = User::query()->where('email', $invitation->email)->firstOrFail();
        $interests = EventUserInterest::query()
            ->where('user_id', $user->id)
            ->whereIn('event_id', $invitation->events()->pluck('events.id'))
            ->with(['user', 'event.eventType'])
            ->get()
            ->map(fn (EventUserInterest $interest): HostContactData => HostContactData::fromInterest($interest))
            ->all();

        return new ContactInvitationResolutionData($status, $interests);
    }
}
