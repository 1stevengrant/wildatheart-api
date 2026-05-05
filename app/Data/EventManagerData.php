<?php

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;
use App\Models\EventManagerInvitation;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventManagerData extends Data
{
    public function __construct(
        public string $id,
        public string $fullname,
        public string $email,
        public string $image,
        public bool $is_pending,
        public ?string $invitation_id = null,
        public bool $is_owner = false,
    ) {}

    public static function fromUser(User $user, bool $isOwner = false): self
    {
        return new self(
            id: $user->id,
            fullname: $user->fullname,
            email: $user->email,
            image: $user->profilePhoto(),
            is_pending: false,
            invitation_id: null,
            is_owner: $isOwner,
        );
    }

    public static function fromInvitation(EventManagerInvitation $invitation): self
    {
        return new self(
            id: $invitation->id,
            fullname: $invitation->email,
            email: $invitation->email,
            image: 'https://ui-avatars.com/api/?name=' . urlencode($invitation->email) . '&background=random',
            is_pending: true,
            invitation_id: $invitation->id,
        );
    }
}
