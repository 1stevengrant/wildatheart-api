<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    const ROLE_ADMIN = 'admin';

    const ROLE_HOST = 'host';

    const ROLE_ATTENDEE = 'attendee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'id' => 'integer',
    ];

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function campfires(): HasMany
    {
        return $this->hasMany(Campfire::class);
    }

    public function isAttending(Campfire $campfire): bool
    {
        return $this->attendees->contains('campfire_id', $campfire->id);
    }

    public function getFullnameAttribute(): string
    {
        return "{$this->given_name} {$this->family_name}";
    }

    public function emailVerifications(): HasMany
    {
        return $this->hasMany(EmailVerify::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function profilePhoto(): string
    {
        if (! app()->environment('local')) {
            if ($this->image) {
                makeImagePublic($this->image);

                return $this->image ? getImageUrl($this->image) : asset('svgs/user.svg');
            }

            return asset('svgs/user.svg');

        }

        return $this->image ? asset($this->image) : asset('svgs/user.svg');
    }
}
