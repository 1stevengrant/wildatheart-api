<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContactInvitation extends BaseModel
{
    use HasUlids;

    protected $fillable = [
        'invited_by_user_id', 'existing_user_id', 'email', 'given_name', 'family_name', 'phone',
        'staged_image_path', 'token_hash', 'active_key', 'expires_at', 'accepted_at', 'declined_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function existingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'existing_user_id');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'contact_invitation_event');
    }

    public function isPending(): bool
    {
        return ! $this->accepted_at && ! $this->declined_at && ! $this->cancelled_at && $this->expires_at->isFuture();
    }

    public static function tokenHash(string $token): string
    {
        return hash('sha256', $token);
    }
}
