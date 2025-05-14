<?php

namespace App\Builders;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function byEmail($email): UserBuilder
    {
        return $this->where('email', $email);
    }

    public function byId($id): UserBuilder
    {
        return $this->where('id', $id);
    }

    public function byLegacyDrupalId($legacyDrupalId): UserBuilder
    {
        return $this->where('legacy_drupal_id', $legacyDrupalId);
    }

    public function verified(): UserBuilder
    {
        return $this->whereNotNull('email_verified_at');
    }

    public function unverified(): UserBuilder
    {
        return $this->whereNull('email_verified_at');
    }

    public function hasEvents(): UserBuilder
    {
        return $this->whereHas('events');
    }

    public function hasNoEvents(): UserBuilder
    {
        return $this->whereDoesntHave('events');
    }

    public function optedIntoMarketing(): UserBuilder
    {
        return $this->where('opted_into_marketing', true);
    }

    public function withIncompleteProfilesAndEvents(): Collection
    {
        $incompleteUserIds = DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->where(function ($query) {
                $query->whereNull('users.family_name')
                    ->orWhere('users.family_name', '')
                    ->orWhereNull('users.given_name')
                    ->orWhere('users.given_name', '')
                    ->orWhereNull('users.phone')
                    ->orWhere('users.phone', '');
            })
            ->select('users.id')
            ->distinct()
            ->pluck('id');

        return User::whereIn('id', $incompleteUserIds)
            ->get()
            ->map(function ($user) {
                $missingFields = [];

                if (empty($user->family_name)) {
                    $missingFields[] = 'family_name';
                }

                if (empty($user->given_name)) {
                    $missingFields[] = 'given_name';
                }

                if (empty($user->phone)) {
                    $missingFields[] = 'phone';
                }

                return [
                    'id' => $user->id,
                    'email' => $user->email ?? 'No Email',
                    'missing_fields' => $missingFields,
                ];
            });
    }

    public function withCompleteProfilesAndEvents(): UserBuilder
    {
        $completeUserIds = DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->whereNotNull('users.family_name')
            ->where('users.family_name', '!=', '')
            ->whereNotNull('users.given_name')
            ->where('users.given_name', '!=', '')
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '')
            ->select('users.id')
            ->distinct()
            ->pluck('id');

        return $this->whereIn('id', $completeUserIds);
    }

    public function hasNeverLoggedIn(): UserBuilder
    {
        return $this->whereNull('last_login_at');
    }

    public function byWeGiveId(int $weGiveId): UserBuilder
    {
        return $this->where('wegive_donor_id', $weGiveId);
    }

    public function hasNoWeGiveId(): UserBuilder
    {
        return $this->whereNull('wegive_donor_id');
    }

    public function hasNoProviderId(): UserBuilder
    {
        return $this->whereNull('provider_id');
    }

    public function hasNoProvider(): UserBuilder
    {
        return $this->whereNull('provider');
    }

    public function hasProvider(): UserBuilder
    {
        return $this->whereNotNull('provider');
    }

    public function byVerificationToken(string $verificationToken): UserBuilder
    {
        return $this->where('email_verification_token', $verificationToken);

    }
}
