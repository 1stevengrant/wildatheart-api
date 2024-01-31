<?php

namespace App\Builders;

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
}
